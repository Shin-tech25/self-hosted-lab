//+------------------------------------------------------------------+
//| Phantom.mq4  (API-driven Grid EA, fixed job-lot & safe fallback) |
//| - Djangoと連携: claim / status poll / complete / error           |
//| - 境界到達またはサーバ指示で全クローズ                           |
//| - 通知は Push のみ（NOTIFY_PUSH=true のとき）                    |
//| - Job確定時にロットを一度だけ算出・固定、欠け分のみ補填          |
//| - 近すぎエラーに対して最小距離スナップ＋軽い再試行を実装         |
//+------------------------------------------------------------------+
#property strict

// ======================== Inputs ==================================
input string API_BASE    = "https://api.example.com"; // API Base URL（末尾の / は不要）
input string API_KEY     = "YOUR_API_KEY";            // 認証キー（Authorization: Api-Key）
input string ACCOUNT_ID  = "YOUR_ACCOUNT";            // 論理/口座ID
input bool   NOTIFY_PUSH = true;                      // MT4 Push 通知

// マージン安全係数（FreeMarginの何割までを使うか）
input double MARGIN_SAFETY = 0.95;                    // 0.90〜0.98 推奨
// Tick準備待ちパラメータ
input int    TICK_RETRIES  = 15;                      // 15回
input int    TICK_WAIT_MS  = 200;                     // 200ms × 15 = 最大3秒

// ===================== Runtime Params =============================
int     JobId=-1; string JobStatus=""; string JobSymbol="";
int     RtMagic=0; string RtSide="BUY"; double RtSLPrice=0, RtTPPrice=0;
bool    RtUseRiskLot=true; double RtRiskPercent=2.0, RtLotsFixed=0.1, RtMaxLotCap=10.0;
int     RtSlippage=3; double RtTolPricePips=2.5; int RtCooldownSec=8;
bool    IsActive=false, PausedBoundary=false; datetime _lastClaimTry=0, _lastStatusPoll=0;
int     _sendFailCount=0, _sendFailLimit=3;

// === Job 固有パラメータ（本仕様の中心） ============================
int     RtPlannedSlots=6;     // 想定スロット数（デフォ: Q1×3, MID×2, Q3×1）
double  RtJobLot=0.0;         // Job確定時に一度だけ算出・固定するロット

// ======================== Utils ===================================
double PipFor(string sym){
   int d=(int)MarketInfo(sym,MODE_DIGITS); double pt=MarketInfo(sym,MODE_POINT);
   return (d==3||d==5)?pt*10:pt;
}
double NpFor(string sym,double p){ return NormalizeDouble(p,(int)MarketInfo(sym,MODE_DIGITS)); }
bool   IsPendingType(int t){ return (t==OP_BUYLIMIT||t==OP_BUYSTOP||t==OP_SELLLIMIT||t==OP_SELLSTOP); }
void   LogErr(string where,int code){ Print(where," failed. err=",code); }
string _fmt5(double v){ return DoubleToString(v,5); }

// --- cooldown / GV helpers
string GVName(const string tag){ return StringFormat("GRID_CD_%s_%s_%d",JobSymbol,tag,RtMagic); }
bool   CooldownPassed(const string tag){
   string n=GVName(tag); double v=GlobalVariableGet(n);
   if(v==0) return true;
   return (TimeCurrent()-(int)v)>=RtCooldownSec;
}
void   TouchCooldown(const string tag){ GlobalVariableSet(GVName(tag),TimeCurrent()); }

// ===== Tick readiness =============================================
// ※ デフォルト引数は使わない（input変数は使えないため）
bool EnsureTickReady(const string sym, int retries, int sleep_ms){
   if(!SymbolSelect(sym,true)){
      Print("SymbolSelect failed: ", sym, " err=",GetLastError());
      return false;
   }
   for(int i=0;i<retries;i++){
      RefreshRates();
      double ts=MarketInfo(sym,MODE_TICKSIZE);
      double tv=MarketInfo(sym,MODE_TICKVALUE);
      if(ts>0 && tv>0) return true;
      Sleep(sleep_ms);
   }
   Print("Tick not ready (TickSize/TickValue invalid). sym=",sym,
         " ts=",DoubleToString(MarketInfo(sym,MODE_TICKSIZE),6),
         " tv=",DoubleToString(MarketInfo(sym,MODE_TICKVALUE),6));
   return false;
}

// ====================== HTTP (JSON) ================================
bool HttpRequest(const string method,const string url,const string body,string &resp,int timeout=7000){
   char post[]; char result[]; string resHdr="";
   string hdr="Content-Type: application/json\r\nAccept: application/json\r\nAuthorization: Api-Key "+API_KEY+"\r\n\r\n";
   if(body!=""){ int n=StringLen(body); ArrayResize(post,n); StringToCharArray(body,post,0,n,CP_UTF8); }
   ResetLastError();
   int st=WebRequest(method,url,hdr,timeout,post,result,resHdr);
   resp=CharArrayToString(result,0,ArraySize(result));
   if(st==-1){ Print("WebRequest error=",GetLastError()); return false; }
   if(st>=200 && st<300) return true;
   Print("HTTP ",method," ",url," -> ",st," resp=",resp); return false;
}
bool HttpPOST(string path,string body,string &resp){ return HttpRequest("POST",API_BASE+path,body,resp); }
bool HttpGET (string path,           string &resp){ return HttpRequest("GET", API_BASE+path,"",resp);  }

// ======================= JSON tiny get =============================
string _Trim(const string s){ return StringTrimLeft(StringTrimRight(s)); }
string JsonGetStr(string j,string k,string dv=""){
   string pat="\""+k+"\""; int i=StringFind(j,pat); if(i<0) return dv;
   int q1=StringFind(j,"\"",i+StringLen(pat)); if(q1<0) return dv;
   int q2=StringFind(j,"\"",q1+1); if(q2<0) return dv;
   return StringSubstr(j,q1+1,q2-q1-1);
}
double JsonGetNum(string j,string k,double dv=0.0){
   string pat="\""+k+"\""; int i=StringFind(j,pat); if(i<0) return dv;
   int c=StringFind(j,":",i+StringLen(pat)); if(c<0) return dv;
   int p=c+1; while(p<StringLen(j) && (j[p]==' '||j[p]=='\t'||j[p]=='\r'||j[p]=='\n')) p++;
   int q=p;   while(q<StringLen(j) && j[q]!=',' && j[q]!='}' && j[q]!=']') q++;
   string raw=_Trim(StringSubstr(j,p,q-p)); if(raw=="null"||raw=="") return dv;
   if(StringLen(raw)>=2 && raw[0]=='"' && raw[StringLen(raw)-1]=='"') raw=StringSubstr(raw,1,StringLen(raw)-2);
   return StringToDouble(raw);
}
bool JsonGetBool(string j,string k,bool dv=false){
   string s=JsonGetStr(j,k,dv?"true":"false"); if(s=="true") return true; if(s=="false") return false;
   string pat="\""+k+"\""; int i=StringFind(j,pat); if(i<0) return dv;
   int c=StringFind(j,":",i+StringLen(pat)); if(c<0) return dv;
   int p=c+1; while(p<StringLen(j) && (j[p]==' '||j[p]=='\t'||j[p]=='\r'||j[p]=='\n')) p++;
   if(StringSubstr(j,p,4)=="true") return true; if(StringSubstr(j,p,5)=="false") return false; return dv;
}

// ======================= Notify (Push only) ========================
void NotifyStart(){
   if(!NOTIFY_PUSH) return;
   SendNotification(StringFormat("[Phantom] START job=%d %s %s SL=%s TP=%s magic=%d",
      JobId, JobSymbol, RtSide, _fmt5(RtSLPrice), _fmt5(RtTPPrice), RtMagic));
}
void NotifyEnd(const string reason){
   if(!NOTIFY_PUSH) return;
   SendNotification(StringFormat("[Phantom] END(%s) job=%d %s %s magic=%d",
      reason, JobId, JobSymbol, RtSide, RtMagic));
}
void NotifyError(const string detail){
   if(!NOTIFY_PUSH) return;
   SendNotification(StringFormat("[Phantom] ERROR job=%d %s", JobId, detail));
}

// ======================= Trade helpers =============================
int PendingTypeFor(double lv,double bid,double ask){
   return (RtSide=="BUY") ? (lv<=ask?OP_BUYLIMIT:OP_BUYSTOP)
                          : (lv>=bid?OP_SELLLIMIT:OP_SELLSTOP);
}
string SlotTag(const string level,int k){ return StringFormat("GRID %s k%d",level,k); }

int FindOpenByTag(const string tag){
   for(int i=OrdersTotal()-1;i>=0;i--){
      if(!OrderSelect(i,SELECT_BY_POS,MODE_TRADES)) continue;
      if(OrderMagicNumber()!=RtMagic||OrderSymbol()!=JobSymbol) continue;
      int t=OrderType(); if((t==OP_BUY||t==OP_SELL)&&OrderComment()==tag) return OrderTicket();
   }
   return -1;
}
int FindPendingByTag(const string tag){
   for(int i=OrdersTotal()-1;i>=0;i--){
      if(!OrderSelect(i,SELECT_BY_POS,MODE_TRADES)) continue;
      if(OrderMagicNumber()!=RtMagic||OrderSymbol()!=JobSymbol) continue;
      if(IsPendingType(OrderType()) && OrderComment()==tag) return OrderTicket();
   }
   return -1;
}

double NormalizeLotToBroker(double lot){
   double minLot=MarketInfo(JobSymbol,MODE_MINLOT), maxLot=MarketInfo(JobSymbol,MODE_MAXLOT), step=MarketInfo(JobSymbol,MODE_LOTSTEP);
   if(step<=0) step=0.01;
   if(lot>RtMaxLotCap) lot=RtMaxLotCap;
   if(lot>maxLot) lot=maxLot;
   if(lot<minLot) lot=minLot;
   lot=MathFloor(lot/step)*step;
   if(lot<minLot) lot=minLot;
   return NormalizeDouble(lot,2);
}

// --- broker constraints helpers -----------------------------------
double PointFor(const string sym){ return MarketInfo(sym, MODE_POINT); }

double MinStopDistancePoints(const string sym){
   double pt   = PointFor(sym);
   int    slvl = (int)MarketInfo(sym, MODE_STOPLEVEL);   // broker指定(ポイント)
   return MathMax(0, slvl) * pt;
}

double FreezeDistancePoints(const string sym){
   double pt    = PointFor(sym);
   int    flvl  = (int)MarketInfo(sym, MODE_FREEZELEVEL);
   return MathMax(0, flvl) * pt;
}

// ptypeに応じてentryを最小距離ぶん離す。返り値<=0 なら不成立（スキップ推奨）
double SnapEntryToValidDistance(const string sym, int ptype, double desired){
   RefreshRates();
   double bid = MarketInfo(sym, MODE_BID);
   double ask = MarketInfo(sym, MODE_ASK);
   double mind= MinStopDistancePoints(sym);

   if(ptype==OP_BUYLIMIT){
      if(ask - desired < mind) desired = ask - mind;
   }else if(ptype==OP_BUYSTOP){
      if(desired - ask < mind) desired = ask + mind;
   }else if(ptype==OP_SELLLIMIT){
      if(desired - bid < mind) desired = bid + mind;
   }else if(ptype==OP_SELLSTOP){
      if(bid - desired < mind) desired = bid - mind;
   }
   return NpFor(sym, desired);
}

// 1ロットで「SL〜TPの1/4だけ逆行」したときの損失額（口座通貨）
double PerLotLossForQuarterRange(){
   double ts=MarketInfo(JobSymbol,MODE_TICKSIZE), tv=MarketInfo(JobSymbol,MODE_TICKVALUE);
   if(ts<=0 || tv<=0) return 0.0;
   double qr=MathAbs(RtTPPrice-RtSLPrice)/4.0;
   return (qr/ts)*tv;
}

// --- 方向・全クローズ・境界判定
bool DirectionOK(){
   if(RtSide=="BUY"  && !(RtTPPrice>RtSLPrice)) { Print("BUYならTP>SL"); return false; }
   if(RtSide=="SELL" && !(RtTPPrice<RtSLPrice)) { Print("SELLならTP<SL"); return false; }
   if(RtSLPrice<=0 || RtTPPrice<=0){ Print("境界値0不可"); return false; }
   return true;
}
bool CancelAllPendingsAndCloseAllByMagic(){
   bool ok=true;
   for(int i=OrdersTotal()-1;i>=0;i--){
      if(!OrderSelect(i,SELECT_BY_POS,MODE_TRADES)) continue;
      if(OrderSymbol()!=JobSymbol || OrderMagicNumber()!=RtMagic) continue;
      int t=OrderType();
      if(IsPendingType(t)){
         if(!OrderDelete(OrderTicket())){ LogErr("OrderDelete",GetLastError()); ok=false; }
      }else if(t==OP_BUY || t==OP_SELL){
         double px=(t==OP_BUY?MarketInfo(JobSymbol,MODE_BID):MarketInfo(JobSymbol,MODE_ASK));
         if(!OrderClose(OrderTicket(),OrderLots(),px,RtSlippage,clrRed)){ LogErr("OrderClose",GetLastError()); ok=false; }
      }
   }
   return ok;
}
bool PriceHitBoundary(){
   double lo=MathMin(RtSLPrice,RtTPPrice), hi=MathMax(RtSLPrice,RtTPPrice);
   double bid=MarketInfo(JobSymbol,MODE_BID), ask=MarketInfo(JobSymbol,MODE_ASK);
   return (ask<=lo || bid>=hi);
}

// ===== ロット計算：リスク基準（理論値） =============================
double CalcRiskLotCore(){
   if(!RtUseRiskLot) return NormalizeLotToBroker(RtLotsFixed);
   double per=PerLotLossForQuarterRange();
   if(per<=0) return 0.0;  // 固定ロットに落とさず中止（安全）
   double maxDD=AccountEquity()*(RtRiskPercent/100.0);
   return NormalizeLotToBroker(maxDD/(10*per));
}

// ===== ロット計算：証拠金で上限キャップ ===========================
double CapLotByMargin(double lot_base, int grid_slots){
   if(lot_base <= 0) return 0.0;
   double mreq = MarketInfo(JobSymbol, MODE_MARGINREQUIRED);
   if(mreq <= 0) mreq = MarketInfo(JobSymbol, MODE_MARGININIT);
   double fm   = AccountFreeMargin();
   if(mreq <= 0 || fm <= 0) return 0.0;

   double max_total_lot = (fm * MARGIN_SAFETY) / mreq;
   int slots = MathMax(1, grid_slots); // ゼロ割・過小割り防止
   double max_per_slot  = max_total_lot / slots;

   double final_lot = MathMin(lot_base, max_per_slot);
   return NormalizeLotToBroker(final_lot);
}

// ======================= Django API ================================
bool ReportError(const string detail){
   if(JobId<=0) { NotifyError(detail); return false; }
   string d=detail; StringReplace(d,"\\","\\\\"); StringReplace(d,"\"","\\\"");
   string body=StringFormat("{\"error_detail\":\"%s\"}",d), dummy;
   bool ok=HttpPOST(StringFormat("/phantom-jobs/%d/error/",JobId),body,dummy);
   if(!ok) Print("ReportError failed: ",dummy);
   NotifyError(detail);
   return ok;
}
bool ClaimLatestPending(){
   string cur=Symbol();
   string body="{\"account_id\":\""+ACCOUNT_ID+"\",\"symbol\":\""+cur+"\"}";
   string resp; if(!HttpPOST("/phantom-jobs/claim/",body,resp)) return false;
   if(StringLen(resp)==0) return false;

   JobId=(int)JsonGetNum(resp,"id",-1);
   JobStatus=JsonGetStr(resp,"status","");
   JobSymbol=JsonGetStr(resp,"symbol",""); if(JobSymbol=="") JobSymbol=cur;

   RtMagic       =(int)JsonGetNum(resp,"magic", TimeCurrent());
   RtSide        =JsonGetStr(resp,"side","BUY");
   RtSLPrice     =JsonGetNum(resp,"sl_price",0.0);
   RtTPPrice     =JsonGetNum(resp,"tp_price",0.0);
   RtUseRiskLot  =JsonGetBool(resp,"use_risk_lot",true);
   RtRiskPercent =JsonGetNum(resp,"risk_percent",2.0);
   RtLotsFixed   =JsonGetNum(resp,"lots_fixed",0.10);
   RtMaxLotCap   =JsonGetNum(resp,"max_lot_cap",10.0);
   RtSlippage    =(int)JsonGetNum(resp,"slippage",3);
   RtTolPricePips=JsonGetNum(resp,"tol_price_pips",2.5);
   RtCooldownSec =(int)JsonGetNum(resp,"cooldown_sec",8);
   RtPlannedSlots=(int)JsonGetNum(resp,"planned_slots",6); // API側が返せるなら利用

   // --- Tick準備チェック（ここで失敗ならアボート）
   if(!EnsureTickReady(JobSymbol, TICK_RETRIES, TICK_WAIT_MS)){
      int e=GetLastError();
      string msg=StringFormat("Tick not ready after claim. sym=%s err=%d",JobSymbol,e);
      Print(msg); ReportError(msg); JobId=-1; JobStatus=""; IsActive=false; return false;
   }

   if(!(RtTPPrice>0&&RtSLPrice>0)||!DirectionOK()){
      string msg=StringFormat("Invalid params: side=%s sl=%G tp=%G",RtSide,RtSLPrice,RtTPPrice);
      Print(msg); ReportError(msg); JobId=-1; JobStatus=""; IsActive=false; return false;
   }

   IsActive=(JobId>0 && JobStatus=="RUNNING");
   if(IsActive){
      Print("CLAIMED job id=",JobId," magic=",RtMagic," side=",RtSide," symbol=",JobSymbol," status=",JobStatus);
      PausedBoundary=false; _sendFailCount=0; NotifyStart();

      // === Job確定時に "一度だけ" ロット算出・固定 ==================
      double lot_base = CalcRiskLotCore();
      if(lot_base<=0){
         ReportError("risk-lot<=0 at claim. abort.");
         IsActive=false; JobId=-1; JobStatus=""; return false;
      }
      RtJobLot = CapLotByMargin(lot_base, RtPlannedSlots);
      if(RtJobLot<=0){
         ReportError("job-lot<=0 at claim. abort.");
         IsActive=false; JobId=-1; JobStatus=""; return false;
      }
      Print(StringFormat("[JOBLOT] job=%d sym=%s magic=%d planned=%d lot=%.2f",
            JobId, JobSymbol, RtMagic, RtPlannedSlots, RtJobLot));
   }
   return IsActive;
}
bool FetchJobStatus(){
   if(JobId<=0) return false;
   string resp; if(!HttpGET(StringFormat("/phantom-jobs/%d/",JobId),resp)) return false;
   JobStatus=JsonGetStr(resp,"status",JobStatus); return true;
}
bool MarkCompleted(){
   if(JobId<=0) return false;
   string dummy; bool ok=HttpPOST(StringFormat("/phantom-jobs/%d/complete/",JobId),"{}",dummy);
   if(!ok) Print("MarkCompleted failed: ",dummy);
   return ok;
}

// ======================= Grid maintenance ==========================
bool EnsureSlotPending(const string tag,int ptype,double entry,double sl,double tp,double tol_price,double tol_tp,double desireLot){
   if(FindOpenByTag(tag)!=-1) return true;

   int tk=FindPendingByTag(tag);
   if(tk==-1){
      if(!CooldownPassed(tag)) return true;

      // ★最新レートで方向を再判定し、エントリー価格を最小距離へスナップ
      RefreshRates();
      double bid0=MarketInfo(JobSymbol,MODE_BID), ask0=MarketInfo(JobSymbol,MODE_ASK);
      int ptype_now = PendingTypeFor(entry, bid0, ask0);
      if(ptype_now != ptype) ptype = ptype_now;

      double entry_adj = SnapEntryToValidDistance(JobSymbol, ptype, entry);
      if(entry_adj <= 0){
         TouchCooldown(tag);  // 近すぎて調整不能→一旦待つ
         return false;
      }

      int ntk=OrderSend(JobSymbol,ptype,desireLot,entry_adj,RtSlippage,sl,tp,tag,RtMagic,0,clrDodgerBlue);
      if(ntk>0){ _sendFailCount=0; TouchCooldown(tag); return true; }

      int err=GetLastError();

      // 129/130/136/138/146 は1回だけ軽い再試行
      bool retryable = (err==129 || err==130 || err==136 || err==138 || err==146);
      if(retryable){
         RefreshRates(); Sleep(TICK_WAIT_MS);
         double entry_retry = SnapEntryToValidDistance(JobSymbol, ptype, entry);
         if(entry_retry>0){
            int ntk2=OrderSend(JobSymbol, ptype, desireLot, entry_retry, RtSlippage, sl, tp, tag, RtMagic, 0, clrDodgerBlue);
            if(ntk2>0){ _sendFailCount=0; TouchCooldown(tag); return true; }
            err = GetLastError();
         }
      }

      // フォールバック：資金不足（134）の場合は今回スロットだけ安全縮小
      if(err==134){
         double one_cap = CapLotByMargin(desireLot, 1);
         if(one_cap>0 && one_cap<desireLot){
            int rt=OrderSend(JobSymbol,ptype,one_cap,entry_adj,RtSlippage,sl,tp,tag,RtMagic,0,clrDodgerBlue);
            if(rt>0){
               _sendFailCount=0; TouchCooldown(tag);
               Print(StringFormat("fallback lot shrink tag=%s %.2f->%.2f", tag, desireLot, one_cap));
               return true;
            }
            err=GetLastError();
         }
      }

      _sendFailCount++;
      string msg=StringFormat("OrderSend failed tag=%s err=%d",tag,err); Print(msg);
      if(_sendFailCount>=_sendFailLimit){
         ReportError(StringFormat("OrderSend consecutive fail >=%d. last=%s",_sendFailLimit,msg));
         IsActive=false; JobId=-1; JobStatus="";
      }
      return false;
   }

   // 既存Pendingがある → 価格系のみ追従（ロットは触らない：OrderModifyでは変更不可）
   if(!OrderSelect(tk,SELECT_BY_TICKET)) return false;
   double cp=OrderOpenPrice(), csl=OrderStopLoss(), ctp=OrderTakeProfit();
   bool priceOff=(MathAbs(cp-entry)>tol_price), slOff=(MathAbs(csl-sl)>tol_price), tpOff=(MathAbs(ctp-tp)>tol_tp);

   if(priceOff||slOff||tpOff){
      // 再スナップしてから modify を試みる
      double entry_mod = SnapEntryToValidDistance(JobSymbol, ptype, entry);
      if(entry_mod<=0) return false;

      if(!OrderModify(tk,entry_mod,sl,tp,0,clrDodgerBlue)){
         int errm=GetLastError();

         // 軽い再試行（129/130/136/138/146）
         if(errm==129 || errm==130 || errm==136 || errm==138 || errm==146){
            RefreshRates(); Sleep(TICK_WAIT_MS);
            entry_mod = SnapEntryToValidDistance(JobSymbol, ptype, entry);
            if(entry_mod>0 && OrderModify(tk, entry_mod, sl, tp, 0, clrDodgerBlue)) return true;
         }

         // だめなら削除→再発注（cooldown尊重）
         if(!CooldownPassed(tag)) return false;
         if(!OrderDelete(tk)) return false;

         int ntk=OrderSend(JobSymbol,ptype,desireLot,entry_mod,RtSlippage,sl,tp,tag,RtMagic,0,clrDodgerBlue);
         if(ntk>0){ _sendFailCount=0; TouchCooldown(tag); return true; }

         int err2=GetLastError();
         if(err2==134){
            double one_cap = CapLotByMargin(desireLot, 1);
            if(one_cap>0 && one_cap<desireLot){
               int rt=OrderSend(JobSymbol,ptype,one_cap,entry_mod,RtSlippage,sl,tp,tag,RtMagic,0,clrDodgerBlue);
               if(rt>0){
                  _sendFailCount=0; TouchCooldown(tag);
                  Print(StringFormat("fallback lot shrink (reorder) tag=%s %.2f->%.2f", tag, desireLot, one_cap));
                  return true;
               }
               err2=GetLastError();
            }
         }
         _sendFailCount++;
         string msg2=StringFormat("ReOrder failed tag=%s err=%d",tag,err2); Print(msg2);
         if(_sendFailCount>=_sendFailLimit){
            ReportError(StringFormat("OrderSend consecutive fail >=%d. last=%s",_sendFailLimit,msg2));
            IsActive=false; JobId=-1; JobStatus="";
         }
         return false;
      }
   }
   return true;
}

void MaintainGridSlots(){
   // --- 計算前にも Tick 準備を確認
   if(!EnsureTickReady(JobSymbol, TICK_RETRIES, TICK_WAIT_MS)){
      ReportError("Tick not ready in MaintainGridSlots. skip.");
      IsActive=false; JobId=-1; JobStatus=""; return;
   }

   // ★ Job確定時に固定したロットで、欠けた指値のみ補填
   if(RtJobLot<=0){ Print("RtJobLot<=0 -> skip."); return; }

   double a=RtSLPrice,b=RtTPPrice,range=MathAbs(b-a),sign=(RtSide=="BUY"?+1.0:-1.0);
   double q1=NpFor(JobSymbol,a+sign*range*0.25), mid=NpFor(JobSymbol,a+sign*0.5*range), q3=NpFor(JobSymbol,a+sign*0.75*range);
   double bid=MarketInfo(JobSymbol,MODE_BID), ask=MarketInfo(JobSymbol,MODE_ASK);
   double tol=MathMax(RtTolPricePips*PipFor(JobSymbol),MarketInfo(JobSymbol,MODE_POINT)*2);

   struct L{string n; double p; int kmax;}; L ls[3];
   ls[0].n="Q1"; ls[0].p=q1;  ls[0].kmax=3;
   ls[1].n="MID";ls[1].p=mid; ls[1].kmax=2;
   ls[2].n="Q3"; ls[2].p=q3;  ls[2].kmax=1;

   for(int i=0;i<3;i++){
      int ptype=PendingTypeFor(ls[i].p,bid,ask);
      for(int k=1;k<=ls[i].kmax;k++){
         double tp_off=sign*(range*(k/4.0));
         double sl_for=NpFor(JobSymbol,RtSLPrice);
         double tp_for=NpFor(JobSymbol,ls[i].p+tp_off);
         EnsureSlotPending(SlotTag(ls[i].n,k), ptype, ls[i].p, sl_for, tp_for, tol, tol, RtJobLot);
      }
   }
}

// ======================== Lifecycle ================================
int OnInit(){
   Print("Phantom EA starting. ACC=",ACCOUNT_ID," API=",API_BASE," SYMBOL=",Symbol());
   _lastClaimTry=_lastStatusPoll=0; JobId=-1; JobStatus=""; IsActive=false; PausedBoundary=false; _sendFailCount=0;
   RtJobLot=0.0; RtPlannedSlots=6;
   ClaimLatestPending();
   return INIT_SUCCEEDED;
}

int start(){
   if(!IsActive){
      if(TimeCurrent()-_lastClaimTry>=5){
         _lastClaimTry=TimeCurrent();
         ClaimLatestPending();
      }
      return 0;
   }

   if(TimeCurrent()-_lastStatusPoll>=5){
      _lastStatusPoll=TimeCurrent();
      if(FetchJobStatus() && JobStatus=="COMPLETED"){
         Print("Server requested completion -> closing all.");
         CancelAllPendingsAndCloseAllByMagic();
         NotifyEnd("server");
         IsActive=false; JobId=-1; JobStatus="";
         return 0;
      }
   }

   if(!DirectionOK()){
      string msg=StringFormat("Direction violated: side=%s sl=%G tp=%G",RtSide,RtSLPrice,RtTPPrice);
      Print(msg); ReportError(msg);
      IsActive=false; JobId=-1; JobStatus="";
      return 0;
   }

   if(!PausedBoundary && PriceHitBoundary()){
      CancelAllPendingsAndCloseAllByMagic();
      PausedBoundary=true;
      if(MarkCompleted()) Print("Completed reported. job=",JobId);
      NotifyEnd("boundary");
      IsActive=false; JobId=-1; JobStatus="";
      return 0;
   }

   if(PausedBoundary) return 0;

   MaintainGridSlots();
   return 0;
}

void OnDeinit(const int reason){
   if(IsActive){
      CancelAllPendingsAndCloseAllByMagic();
      NotifyEnd("deinit");
   }
}
//+------------------------------------------------------------------+
