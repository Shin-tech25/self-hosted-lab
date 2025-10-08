//+------------------------------------------------------------------+
//|  SyncClosedHistory.mq4  (MT4 -> Django 差分同期)                 |
//+------------------------------------------------------------------+
#property strict

// ======================= Inputs =======================
input string InpApiBase        = "https://api.example.com/api/"; // APIエンドポイント末尾 /
input string InpApiKey         = "YOUR_API_KEY"; // API Key
input string InpAccountId      = "MT5-123456"; // Account ID
input string InpSymbolFilter   = "";     // 空=全シンボル
input int    InpBatchSize      = 100;
input int    InpHttpTimeoutMs  = 15000;  // POST
input int    InpMaxRetry       = 3;
input int    InpRetryWaitMs    = 1500;
input bool   InpVerboseLog     = true;
input bool   InpRunOnInit      = true;
input int    InpTimerSec       = 300;      // 0=無効

// ======================= Globals =======================
string API_BASE, API_KEY, ACCOUNT_ID, SYMBOL_FILTER;
int    BATCH_SIZE, HTTP_TIMEOUT_MS, MAX_RETRY, RETRY_WAIT_MS, TIMER_SEC;
bool   VERBOSE, RUN_ON_INIT;

// ======================= Utils =======================
void VPrint(string msg){ if(VERBOSE) Print("[Sync] ", msg); }

string JsonEscape(string s){
   string out=s;
   StringReplace(out,"\\","\\\\"); StringReplace(out,"\"","\\\"");
   StringReplace(out,"\r","\\r");  StringReplace(out,"\n","\\n");
   StringReplace(out,"\t","\\t");  return out;
}

string Iso8601Z(datetime t){
   MqlDateTime st; TimeToStruct(t,st);
   return StringFormat("%04d-%02d-%02dT%02d:%02d:%02dZ",st.year,st.mon,st.day,st.hour,st.min,st.sec);
}

bool JsonExtractString(string json,string key,string &value){
   string pat="\""+key+"\":\""; int p=StringFind(json,pat); if(p<0) return false;
   p+=StringLen(pat); int q=StringFind(json,"\"",p); if(q<0) return false;
   value=StringSubstr(json,p,q-p); return true;
}

bool JsonExtractLong(string json,string key,long &num){
   string pat="\""+key+"\":";
   int p=StringFind(json,pat); if(p<0) return false; p+=StringLen(pat);
   while(p<StringLen(json) && StringGetChar(json,p)==' ') p++;
   int start=p; while(p<StringLen(json)){
      int ch=StringGetChar(json,p); if((ch>='0' && ch<='9') || ch=='-'){ p++; continue; } break;
   }
   string s=StringSubstr(json,start,p-start); if(StringLen(s)==0) return false;
   num=(long)StrToInteger(s); return true;
}

int IsoZCompare(const string a,const string b){
   int la=StringLen(a), lb=StringLen(b), n=(la<lb?la:lb);
   for(int i=0;i<n;i++){ int ca=StringGetChar(a,i), cb=StringGetChar(b,i);
      if(ca<cb) return -1; if(ca>cb) return +1; }
   if(la<lb) return -1; if(la>lb) return +1; return 0;
}

bool IsNewer(string ct_iso,long tk,string latest_iso,long latest_tk){
   int c=IsoZCompare(ct_iso,latest_iso);
   if(c>0) return true; if(c<0) return false;
   return (tk>latest_tk);
}

// ======================= HTTP =======================
bool HttpRequest(const string method, const string url, const string body,
                 string &resp, int timeout_ms)
{
   // ヘッダー（必要に応じて Accept を付与）
   string hdr = "Content-Type: application/json\r\n"
                "Accept: application/json\r\n"
                "Authorization: Api-Key " + API_KEY + "\r\n\r\n";

   // 本文を char[] に詰める（空ならサイズ0のまま = GET扱い）
   char post[];
   if(StringLen(body) > 0){
      ArrayResize(post, StringLen(body));
      // UTF-8にして char[] へ詰め替え（Phantomと同じやり方）
      StringToCharArray(body, post, 0, StringLen(body), CP_UTF8);
   }

   // 結果受け取り
   char   result[];
   string resp_hdr = "";

   ResetLastError();
   // ★ポイント：サイズ引数なしのオーバーロード
   int st = WebRequest(method, url, hdr, timeout_ms, post, result, resp_hdr);

   // 失敗処理
   if(st == -1){
      Print("WebRequest ", method, " failed: err=", GetLastError(), " url=", url);
      return false;
   }

   // 本文へ変換（ArraySize(result) で長さ指定）
   resp = CharArrayToString(result, 0, ArraySize(result));
   if(st < 200 || st >= 300){
      Print("HTTP ", method, " ", url, " -> ", st, " resp=", resp);
      return false;
   }
   return true;
}

bool HttpGET(string url, string &resp)
{
   // body="" → post配列サイズ0 → GETとして送られる
   return HttpRequest("GET", url, "", resp, 5000);
}

bool HttpPOST(string url, string body, string &resp)
{
   return HttpRequest("POST", url, body, resp, HTTP_TIMEOUT_MS);
}

bool HttpPOSTRetry(string url,string body,string &resp){
   for(int t=0;t<MAX_RETRY;t++){ if(HttpPOST(url,body,resp)) return true; Sleep(RETRY_WAIT_MS); }
   return false;
}

// ======================= Core =======================
bool GetLatestCursor(string account_id,string symbol_filter,string &latest_iso,long &latest_ticket){
   string url=API_BASE+"closed-positions/latest/?account_id="+account_id;
   if(symbol_filter!="") url+="&symbol="+symbol_filter;
   string js; if(!HttpGET(url,js)) return false;
   string iso=""; long tk=0;
   if(!JsonExtractString(js,"latest_close_time",iso)) iso="1900-01-01T00:00:00Z";
   if(!JsonExtractLong(js,"latest_ticket",tk)) tk=0;
   latest_iso=iso; latest_ticket=tk;
   VPrint("Latest cursor: "+latest_iso+" tk="+IntegerToString((int)latest_ticket));
   return true;
}

string BuildJsonItemFromSelected(string account_id){
   int type=OrderType(); if(type!=OP_BUY && type!=OP_SELL) return "";
   string sym=OrderSymbol(); if(SYMBOL_FILTER!="" && sym!=SYMBOL_FILTER) return "";
   string side=(type==OP_BUY?"BUY":"SELL");
   return StringFormat(
      "{\"account_id\":\"%s\",\"ticket\":%I64d,"
      "\"symbol\":\"%s\",\"side\":\"%s\","
      "\"open_price\":%.5f,\"close_price\":%.5f,\"volume\":%.2f,"
      "\"open_time\":\"%s\",\"close_time\":\"%s\","
      "\"profit\":%.2f,\"commission\":%.2f,\"swap\":%.2f,"
      "\"magic\":%I64d,\"comment\":\"%s\"}",
      account_id,(long)OrderTicket(),
      JsonEscape(sym),side,
      OrderOpenPrice(),OrderClosePrice(),OrderLots(),
      Iso8601Z(OrderOpenTime()),Iso8601Z(OrderCloseTime()),
      OrderProfit(),OrderCommission(),OrderSwap(),
      (long)OrderMagicNumber(),JsonEscape(OrderComment())
   );
}

bool SyncClosedPositions(){
   string latest_iso; long latest_tk=0;
   if(!GetLatestCursor(ACCOUNT_ID,SYMBOL_FILTER,latest_iso,latest_tk)){ Print("GetLatestCursor failed"); return false; }

   int total=OrdersHistoryTotal();             // ←★★ 正式名（タイポ修正）
   if(total<=0){ VPrint("No history"); return true; }

   string payload="["; int bat=0, queued=0, posted=0;
   for(int i=0;i<total;i++){
      if(!OrderSelect(i,SELECT_BY_POS,MODE_HISTORY)) continue;
      int type=OrderType(); if(type!=OP_BUY && type!=OP_SELL) continue;
      if(SYMBOL_FILTER!="" && OrderSymbol()!=SYMBOL_FILTER) continue;

      string ct_iso=Iso8601Z(OrderCloseTime());
      long tk=(long)OrderTicket();
      if(!IsNewer(ct_iso,tk,latest_iso,latest_tk)) continue;

      string one=BuildJsonItemFromSelected(ACCOUNT_ID);
      if(one=="") continue;

      if(bat>0) payload+=",";
      payload+=one; bat++; queued++;

      if(bat>=BATCH_SIZE){
         payload+="]";
         string resp;
         if(!HttpPOSTRetry(API_BASE+"closed-positions/bulk/",payload,resp)){ Print("Bulk post failed"); return false; }
         VPrint(StringFormat("Posted %d items",bat));
         posted+=bat; payload="["; bat=0;
      }
   }

   if(bat>0){
      payload+="]";
      string resp;
      if(!HttpPOSTRetry(API_BASE+"closed-positions/bulk/",payload,resp)){ Print("Bulk post failed (tail)"); return false; }
      VPrint(StringFormat("Posted %d items (tail)",bat));
      posted+=bat;
   }

   VPrint(StringFormat("Sync finished. queued=%d posted=%d",queued,posted));
   return true;
}

// ======================= Life cycle =======================
int OnInit(){
   API_BASE=InpApiBase; API_KEY=InpApiKey; ACCOUNT_ID=InpAccountId; SYMBOL_FILTER=InpSymbolFilter;
   BATCH_SIZE=MathMax(1,InpBatchSize); HTTP_TIMEOUT_MS=InpHttpTimeoutMs; MAX_RETRY=MathMax(1,InpMaxRetry);
   RETRY_WAIT_MS=MathMax(100,InpRetryWaitMs); VERBOSE=InpVerboseLog; RUN_ON_INIT=InpRunOnInit; TIMER_SEC=InpTimerSec;
   if(TIMER_SEC>0) EventSetTimer(TIMER_SEC);
   VPrint("Initialized");
   if(RUN_ON_INIT) SyncClosedPositions();
   return(INIT_SUCCEEDED);
}

void OnDeinit(const int reason){
   if(TIMER_SEC>0) EventKillTimer();
   VPrint("Deinit reason="+IntegerToString(reason));
}

void OnTick(){ /* no-op */ }

void OnTimer(){ SyncClosedPositions(); }
