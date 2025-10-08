//+------------------------------------------------------------------+
//|  AccountDailyStatPoster.mq4  (MT4 -> Django 日次スナップ)       |
//|  - 当日(サーバ日付)の実現損益を集計し、Balance/Equity と共に送信 |
//|  - 同日内は何度でも upsert（最後の値が残る）                     |
//+------------------------------------------------------------------+
#property strict

// ======================= Inputs =======================
input string InpApiBase        = "https://api.example.com/forex/api/"; // 末尾 / 必須
input string InpApiKey         = "YOUR_API_KEY";       // Django側 HasAPIKey
input string InpAccountId      = "MT5-123456";         // 論理Account ID
input int    InpTimerSec       = 3600;                 // 1時間ごと
input int    InpHttpTimeoutMs  = 15000;
input int    InpMaxRetry       = 3;
input int    InpRetryWaitMs    = 1500;
input bool   InpVerboseLog     = true;

// ======================= Globals =======================
string API_BASE, API_KEY, ACCOUNT_ID;
int    TIMER_SEC, HTTP_TIMEOUT_MS, MAX_RETRY, RETRY_WAIT_MS;
bool   VERBOSE;

// ======================= Utils =========================
void VPrint(string msg){ if(VERBOSE) Print("[ADS] ", msg); }

string JsonEscape(string s){
   string out=s;
   StringReplace(out,"\\","\\\\"); StringReplace(out,"\"","\\\"");
   StringReplace(out,"\r","\\r");  StringReplace(out,"\n","\\n");
   StringReplace(out,"\t","\\t");  return out;
}

string DateYmd(datetime t){
   MqlDateTime st; TimeToStruct(t, st);
   return StringFormat("%04d-%02d-%02d", st.year, st.mon, st.day);
}

datetime DayStart(datetime t){
   MqlDateTime st; TimeToStruct(t, st);
   st.hour=0; st.min=0; st.sec=0;
   return StructToTime(st);
}

// 当日(サーバ日付)の実現損益合計（profit+swap+commission）
double SumRealizedPnL_Today(){
   datetime now  = TimeCurrent();
   datetime from = DayStart(now);   // 今日 00:00:00
   double pnl = 0.0;

   int total = OrdersHistoryTotal();
   for(int i=0;i<total;i++){
      if(!OrderSelect(i, SELECT_BY_POS, MODE_HISTORY)) continue;
      int type = OrderType();
      if(type!=OP_BUY && type!=OP_SELL) continue;

      datetime ct = OrderCloseTime();
      if(ct < from || ct > now) continue; // 当日内のみ
      pnl += (OrderProfit() + OrderSwap() + OrderCommission());
   }
   return pnl;
}

// ======================= HTTP =========================
bool HttpRequest(const string method, const string url, const string body,
                 string &resp, int timeout_ms)
{
   string hdr = "Content-Type: application/json\r\n"
                "Accept: application/json\r\n"
                "Authorization: Api-Key " + API_KEY + "\r\n\r\n";

   char post[];
   if(StringLen(body) > 0){
      ArrayResize(post, StringLen(body));
      StringToCharArray(body, post, 0, StringLen(body), CP_UTF8);
   }

   char   result[];
   string resp_hdr = "";

   ResetLastError();
   int st = WebRequest(method, url, hdr, timeout_ms, post, result, resp_hdr);

   if(st == -1){
      Print("WebRequest ", method, " failed: err=", GetLastError(), " url=", url);
      return false;
   }

   resp = CharArrayToString(result, 0, ArraySize(result));
   if(st < 200 || st >= 300){
      Print("HTTP ", method, " ", url, " -> ", st, " resp=", resp);
      return false;
   }
   return true;
}

bool HttpPOST(string url, string body, string &resp)
{
   return HttpRequest("POST", url, body, resp, HTTP_TIMEOUT_MS);
}

bool HttpPOSTRetry(string url,string body,string &resp){
   for(int t=0;t<MAX_RETRY;t++){
      if(HttpPOST(url,body,resp)) return true;
      Sleep(RETRY_WAIT_MS);
   }
   return false;
}

// ======================= Core =========================
bool PostTodaySnapshot(){
   datetime now  = TimeCurrent();
   string   ymd  = DateYmd(now);          // サーバ日付
   double   bal  = AccountBalance();
   double   eq   = AccountEquity();
   double   pnl  = SumRealizedPnL_Today(); // 実現損益のみ（含みは含めない）

   // 小数点はサーバ側でDecimalに吸収される想定だが、表示を整える
   string body = StringFormat(
      "{\"account_id\":\"%s\",\"date\":\"%s\","
      "\"balance\":\"%.2f\",\"equity\":\"%.2f\",\"pnl\":\"%.2f\"}",
      JsonEscape(ACCOUNT_ID), ymd, bal, eq, pnl
   );

   string url  = API_BASE + "account-daily-stats/";
   string resp = "";

   bool ok = HttpPOSTRetry(url, body, resp);
   if(ok) VPrint(StringFormat("POST ok date=%s bal=%.2f eq=%.2f pnl=%.2f", ymd, bal, eq, pnl));
   else   VPrint("POST failed: " + resp);
   return ok;
}

// ======================= Life cycle ====================
int OnInit(){
   API_BASE = InpApiBase; API_KEY = InpApiKey; ACCOUNT_ID = InpAccountId;
   TIMER_SEC = InpTimerSec; HTTP_TIMEOUT_MS = InpHttpTimeoutMs;
   MAX_RETRY = MathMax(1, InpMaxRetry); RETRY_WAIT_MS = MathMax(100, InpRetryWaitMs);
   VERBOSE   = InpVerboseLog;

   if(TIMER_SEC > 0) EventSetTimer(TIMER_SEC);
   VPrint("Initialized. Will post every " + IntegerToString(TIMER_SEC) + " sec.");
   // 起動直後にも1回送る（任意）
   PostTodaySnapshot();
   return(INIT_SUCCEEDED);
}

void OnDeinit(const int reason){
   if(TIMER_SEC > 0) EventKillTimer();
   VPrint("Deinit reason=" + IntegerToString(reason));
}

void OnTick(){ /* no-op */ }

void OnTimer(){ PostTodaySnapshot(); }
//+------------------------------------------------------------------+
