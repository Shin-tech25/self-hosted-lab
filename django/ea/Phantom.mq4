//+------------------------------------------------------------------+
//| Phantom.mq4                                                      |
//| API-driven Grid EA (fixed job-lot & safe fallback)               |
//|                                                                  |
//| Description:                                                     |
//| - Integrates with Django: claim / status poll / complete / error |
//| - RESUME対応: claimでRESUME/PENDINGどちらも取得。                 |
//|   RESUME時はDjangoのruntime_params（フラットキー）をそのまま採用 |
//|   PENDING時はロット計算→PATCHでruntime_paramsを保存              |
//| - 境界到達またはサーバ指示(COMPLETED)時のみクローズ              |
//| - Push notifications (when NOTIFY_PUSH=true)                     |
//| - Computes & fixes job lot size once per job; fills only missing |
//|   pending orders                                                 |
//| - Handles "too close" broker errors via min-distance snap + mild |
//|   retry                                                          |
//|                                                                  |
//| Developer:   Shin Mikami                                         |
//| Contact:     (add your email or URL)                             |
//| Repository:  (add your repo URL)                                 |
//|                                                                  |
//| Version:     1.210                                               |
//| Build Date:  2025-10-16                                          |
//| Platform:    MetaTrader 4 (MQL4)                                 |
//| Symbol Mode: Works with 3/5-digit and JPY 2/3-digit quotes       |
//|                                                                  |
//| License:     MIT License (SPDX-License-Identifier: MIT)          |
//|                                                                  |
//| @file        Phantom.mq4                                         |
//| @brief       Grid trading EA coordinated by a Django backend.    |
//| @author      Shin Mikami                                         |
//| @version     1.210                                               |
//| @date        2025-10-16                                          |
//|                                                                  |
//| Requirements:                                                    |
//| - MT4 terminal configured for WebRequest to API_BASE             |
//|   (Tools -> Options -> Expert Advisors -> Allow WebRequest)      |
//| - If EMAIL_MODE="TERMINAL": MT4 Mail settings configured         |
//|   (Tools -> Options -> Email; for Gmail use App Password)        |
//| - If EMAIL_MODE="API": Django endpoint /notify/email accepting   |
//|   {subject, body, to[]} and sending mail server-side             |
//|                                                                  |
//| Inputs Overview:                                                 |
//| - API_BASE (string): Base URL for the Django API                 |
//| - API_KEY  (string): "Authorization: Api-Key" token              |
//| - ACCOUNT_ID (string): Logical account identifier                |
//| - NOTIFY_PUSH (bool): Enable MT4 Push notifications              |
//| - NOTIFY_EMAIL (bool): Send email on start/end/error             |
//| - EMAIL_MODE (string): "TERMINAL" or "API"                       |
//| - EMAIL_TO_API (string): CSV recipients (API mode only)          |
//| - EMAIL_SUBJECT_PREFIX (string): Email subject prefix            |
//| - MARGIN_SAFETY (double): FreeMargin usage cap (0.90–0.98)       |
//| - TICK_RETRIES / TICK_WAIT_MS: Tick readiness wait parameters    |
//|                                                                  |
//| Safety Notes:                                                    |
//| - Direction validation enforces BUY: TP>SL, SELL: TP<SL          |
//| - Margin cap limits per-slot size by required margin             |
//| - Mild retry on transient broker errors (129/130/136/138/146)    |
//| - Fallback lot shrink on insufficient margin (134)               |
//|                                                                  |
//| Change Log:                                                      |
//| - 1.2.1 (2025-10-16): RESUME設計対応。claimでRESUME/PENDING両対応 |
//|   ・RESUME時はruntime_params(フラットキー)をそのまま採用          |
//|   ・PENDING時は従来計算→PATCHでruntime_params保存                 |
//|   ・OnDeinitでの全決済を削除（保持デフォルト）                    |
//| - 1.2.0 (2025-10-11): Email notifications等                      |
//| - 1.1.0: Fixed job-lot computation & margin cap per grid slots   |
//| - 1.0.0: Initial public version                                  |
//|                                                                  |
//| Disclaimer:                                                      |
//| This EA is provided "as is" without warranties of any kind.      |
//| Use at your own risk. Test thoroughly on demo before live use.   |
//+------------------------------------------------------------------+

#property copyright "Shin Mikami"
#property link      "(add your email or URL)"
#property version   "1.210"

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

input bool   NOTIFY_EMAIL = true;               // メール通知（開始/終了/エラーの全て）
input string EMAIL_MODE   = "TERMINAL";         // "TERMINAL" or "API"
input string EMAIL_TO_API = "";                 // APIモード時のみ: 宛先(カンマ区切り)
input string EMAIL_SUBJECT_PREFIX = "[Phantom]";// 件名プリフィクス

// ===================== Runtime Params =============================
int     JobId=-1; string JobStatus=""; string JobSymbol="";
int     RtMagic=0; string RtSide="BUY"; double RtSLPrice=0, RtTPPrice=0;
bool    RtUseRiskLot=true; double RtRiskPercent=2.0, RtLotsFixed=0.1, RtMaxLotCap=10.0;
int     RtSlippage=3; double RtTolPricePips=2.5; int RtCooldownSec=8;
bool    IsActive=false, PausedBoundary=false; datetime _lastClaimTry=0, _lastStatusPoll=0;
int     _sendFailCount=0, _sendFailLimit=3;

// === Job 固有パラメータ ===========================================
int     RtPlannedSlots=6;     // 想定スロット数（デフォ: Q1×3, MID×2, Q3×1）
double  RtJobLot=0.0;         // Job確定時に一度だけ算出 or サーバから取得して固定

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
bool HttpPOST (string path,string body,string &resp){ return HttpRequest("POST" ,API_BASE+path,body,resp); }
bool HttpGET  (string path,           string &resp){ return HttpRequest("GET"  ,API_BASE+path,"",resp);  }
bool HttpPATCH(string path,string body,string &resp){ return HttpRequest("PATCH",API_BASE+path,body,resp); }

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
string JsonEscape(const string s) {
   string t = s;
   StringReplace(t, "\\", "\\\\");
   StringReplace(t, "\"", "\\\"");
   StringReplace(t, "\r", "\\r");
   StringReplace(t, "\n", "\\n");
   return t;
}

// "a,b,c" -> ["a","b","c"] のJSON配列文字列を作る簡易化
string CsvToJsonArray(const string csv) {
   string c = _Trim(csv);
   if(c=="") return "[]";
   string out = "[\"";
   string tmp = c;
   StringReplace(tmp, "\"", "\\\""); // 念のため
   StringReplace(tmp, ",", "\",\"");
   out += tmp + "\"]";
   return out;
}

// ======================= Notify (Push only) ========================
void NotifyStart(){
   if(NOTIFY_PUSH){
      SendNotification(StringFormat("[Phantom] START job=%d %s %s SL=%s TP=%s magic=%d",
         JobId, JobSymbol, RtSide, _fmt5(RtSLPrice), _fmt5(RtTPPrice), RtMagic));
   }
   if(NOTIFY_EMAIL){
      string body = StringFormat(
         "Job STARTED\njob=%d\nsymbol=%s\nside=%s\nSL=%s\nTP=%s\nmagic=%d\n",
         JobId, JobSymbol, RtSide, _fmt5(RtSLPrice), _fmt5(RtTPPrice), RtMagic);
      SendEmail("START", body);
   }
}
void NotifyEnd(const string reason){
   if(NOTIFY_PUSH){
      SendNotification(StringFormat("[Phantom] END(%s) job=%d %s %s magic=%d",
         reason, JobId, JobSymbol, RtSide, RtMagic));
   }
   if(NOTIFY_EMAIL){
      string body = StringFormat(
         "Job ENDED (%s)\njob=%d\nsymbol=%s\nside=%s\nmagic=%d\n",
         reason, JobId, JobSymbol, RtSide, RtMagic);
      SendEmail(StringFormat("END(%s)", reason), body);
   }
}
void NotifyError(const string detail){
   if(NOTIFY_PUSH){
      SendNotification(StringFormat("[Phantom] ERROR job=%d %s", JobId, detail));
   }
   if(NOTIFY_EMAIL){
      string body = StringFormat(
         "Job ERROR\njob=%d\nsymbol=%s\ndetail=%s\n",
         JobId, JobSymbol, detail);
      SendEmail("ERROR", body);
   }
}

// ======================= Email Utils ===============================
bool EmailSendTerminal(const string subject, const string body) {
   ResetLastError();
   bool ok = SendMail(subject, body);   // 受信者はMT4側設定のTo
   if(!ok) Print("SendMail failed. err=", GetLastError());
   return ok;
}

// Django経由 (APIモード)
bool EmailSendViaAPI(const string subject, const string body, const string to_csv) {
   string payload = StringFormat(
      "{\"subject\":\"%s\",\"body\":\"%s\",\"to\":%s}",
      JsonEscape(subject), JsonEscape(body), CsvToJsonArray(to_csv)
   );
   string resp;
   bool ok = HttpPOST("/notify/email", payload, resp);
   if(!ok) Print("EmailSendViaAPI failed resp=", resp);
   return ok;
}

// フラグ判定はしない（常に送信）／呼び出し側で NOTIFY_EMAIL を見る
bool SendEmail(const string subject, const string body) {
   string subj = EMAIL_SUBJECT_PREFIX + " " + subject;
   if(EMAIL_MODE=="TERMINAL") return EmailSendTerminal(subj, body);
   else if(EMAIL_MODE=="API") return EmailSendViaAPI(subj, body, EMAIL_TO_API);
   Print("Unknown EMAIL_MODE=", EMAIL_MODE);
   return false;
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

// runtime_params をPOST保存（PENDINGで初期決定後に1回呼ぶ）
void SaveRuntimeParams(){
   if(JobId <= 0) return;

   string payload = StringFormat(
     "{\"runtime_params\":{\"symbol\":\"%s\",\"side\":\"%s\",\"sl_price\":%G,\"tp_price\":%G,"
     "\"planned_slots\":%d,\"job_lot\":%G,\"slippage\":%d,\"tol_price_pips\":%G,"
     "\"cooldown_sec\":%d,\"max_lot_cap\":%G}}",
     JobSymbol, RtSide, RtSLPrice, RtTPPrice, RtPlannedSlots, RtJobLot, RtSlippage,
     RtTolPricePips, RtCooldownSec, RtMaxLotCap
   );

   string resp;
   // ※ サーバ側に POST /phantom-jobs/<id>/runtime-params/ を用意しておくこと
   bool ok = HttpPOST(StringFormat("/phantom-jobs/%d/runtime-params/", JobId), payload, resp);
   if(!ok){
      Print("SaveRuntimeParams POST failed resp=", resp);
      return;
   }
   // 成功時ログ（任意）
   Print("SaveRuntimeParams posted: ", resp);
}

// claim：PENDING/RESUME両対応（RESUME優先はサーバ側実装）
// RESUME時…フラットキー(job_lot等)が含まれる → それらをそのまま採用
// PENDING時…従来どおりロット算出→PATCHでruntime_params保存
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

   // 追加: フラット化されたruntime_paramsの候補を読む（RESUME時のみ入っている想定）
   int    planned_from_api = (int)JsonGetNum(resp,"planned_slots",-1);
   if(planned_from_api>0) RtPlannedSlots=planned_from_api;
   int    slip_from_api = (int)JsonGetNum(resp,"slippage",-1); if(slip_from_api>=0) RtSlippage=slip_from_api;
   double tol_from_api  = JsonGetNum(resp,"tol_price_pips",-1); if(tol_from_api>0) RtTolPricePips=tol_from_api;
   int    cd_from_api   = (int)JsonGetNum(resp,"cooldown_sec",-1); if(cd_from_api>0) RtCooldownSec=cd_from_api;
   double mlc_from_api  = JsonGetNum(resp,"max_lot_cap",-1); if(mlc_from_api>0) RtMaxLotCap=mlc_from_api;
   double joblot_from_api = JsonGetNum(resp,"job_lot",-1.0);

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

   // サーバはRUNNINGを返す想定。RESUME由来かどうかは job_lot 有無で判定
   bool is_resume = (joblot_from_api>0);

   if(is_resume){
      // === RESUME: サーバ保存のランタイムをそのまま採用
      RtUseRiskLot=false;
      RtJobLot = NormalizeLotToBroker(joblot_from_api);
      IsActive=(JobId>0 && JobStatus=="RUNNING");
      if(IsActive){
         Print(StringFormat("CLAIMED(RESUME) job=%d sym=%s magic=%d lot=%.2f planned=%d",
               JobId, JobSymbol, RtMagic, RtJobLot, RtPlannedSlots));
         PausedBoundary=false; _sendFailCount=0; NotifyStart();
      }
      return IsActive;
   }

   // === PENDING: 従来計算→cap→PATCHでruntime_params保存
   RtUseRiskLot=true;
   IsActive=(JobId>0 && JobStatus=="RUNNING");
   if(IsActive){
      Print("CLAIMED(PENDING) job id=",JobId," magic=",RtMagic," side=",RtSide," symbol=",JobSymbol," status=",JobStatus);
      PausedBoundary=false; _sendFailCount=0; NotifyStart();

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

      // PATCH保存
      SaveRuntimeParams();

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
      if(TimeCurrent()-_lastClaimTry>=60){  // 非アクティブ時 毎分Claimを試みる
         _lastClaimTry=TimeCurrent();
         ClaimLatestPending();
      }
      return 0;
   }

   if(TimeCurrent()-_lastStatusPoll>=60){ // アクティブ時 毎分ステータス確認
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

// OnDeinit: 全決済しない（保持がデフォルト）
void OnDeinit(const int reason){
   if(IsActive){
      // 状態保持のためクローズしない。通知のみ（任意）。
      NotifyEnd("deinit-keep");
   }
}
//+------------------------------------------------------------------+
