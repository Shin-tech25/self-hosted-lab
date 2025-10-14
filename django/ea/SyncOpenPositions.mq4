#property strict

/***********************
 * SETTINGS
 ***********************/
input string API_BASE   = "https://api.example.com/forex/api"; // ← 例: 末尾に /forex/api を含める
input string API_KEY    = "YOUR_API_KEY";
input string ACCOUNT_ID = "YOUR_ACCOUNT";

/***********************
 * LIFECYCLE
 ***********************/
void OnInit()  { EventSetTimer(60); }
void OnDeinit(const int reason) { EventKillTimer(); }

void OnTimer()
{
   string ts = TimeToISO8601Z_UTC(TimeCurrent());

   int ordCount = 0;
   string ordItems   = BuildOrdersItemsJson(ordCount);
   string ordPayload = StringFormat("{\"account_id\":\"%s\",\"snapshot_ts\":\"%s\",\"items\":%s}",
                                    ACCOUNT_ID, ts, ordItems);
   string body1;
   int sc1 = HttpPost(API_BASE + "/open-orders/replace/", ordPayload, body1);
   if(sc1 < 200 || sc1 >= 300)
      Print("open-orders replace failed: status=", sc1, " body=", TruncStr(body1, 400));
   else
      Print("open-orders replace ok: status=", sc1, " count=", ordCount);

   int posCount = 0;
   string posItems   = BuildPositionsItemsJson(posCount);
   string posPayload = StringFormat("{\"account_id\":\"%s\",\"snapshot_ts\":\"%s\",\"items\":%s}",
                                    ACCOUNT_ID, ts, posItems);
   string body2;
   int sc2 = HttpPost(API_BASE + "/open-positions/replace/", posPayload, body2);
   if(sc2 < 200 || sc2 >= 300)
      Print("open-positions replace failed: status=", sc2, " body=", TruncStr(body2, 400));
   else
      Print("open-positions replace ok: status=", sc2, " count=", posCount);
}

/***********************
 * BUILD JSON
 ***********************/
string BuildOrdersItemsJson(int &out_count){
   out_count = 0;
   string j = "[";
   bool first = true;
   int total = OrdersTotal();

   for(int i=0;i<total;i++){
      if(!OrderSelect(i, SELECT_BY_POS, MODE_TRADES)) continue;
      int t = OrderType();
      if(t!=OP_BUYLIMIT && t!=OP_BUYSTOP && t!=OP_SELLLIMIT && t!=OP_SELLSTOP) continue;

      if(!first) j += ",";
      first=false;

      string otype = (t==OP_BUYLIMIT)?"BUYLIMIT":(t==OP_BUYSTOP)?"BUYSTOP":(t==OP_SELLLIMIT)?"SELLLIMIT":"SELLSTOP";
      string side  = (t==OP_BUYLIMIT || t==OP_BUYSTOP)?"BUY":"SELL";

      j += StringFormat(
        "{\"ticket\":%d,\"symbol\":\"%s\",\"side\":\"%s\",\"otype\":\"%s\",\"volume\":%G,"
        "\"price\":%G,\"sl\":%G,\"tp\":%G,\"magic\":%d,"
        "\"comment\":\"%s\",\"placed_at\":\"%s\"}",
        OrderTicket(), OrderSymbol(), side, otype, OrderLots(),
        OrderOpenPrice(), OrderStopLoss(), OrderTakeProfit(), OrderMagicNumber(),
        JsonEscape(TruncStr(OrderComment(), 64)),
        TimeToISO8601Z_UTC(OrderOpenTime())
      );
      out_count++;                 // ← 要素追加のたびに正しく+1
   }
   j += "]";
   return j;
}

string BuildPositionsItemsJson(int &out_count){
   out_count = 0;
   string j = "[";
   bool first = true;
   int total = OrdersTotal();

   for(int i=0;i<total;i++){
      if(!OrderSelect(i, SELECT_BY_POS, MODE_TRADES)) continue;
      int t = OrderType();
      if(t!=OP_BUY && t!=OP_SELL) continue;

      if(!first) j += ",";
      first=false;

      string side = (t==OP_BUY)?"BUY":"SELL";
      j += StringFormat(
        "{\"ticket\":%d,\"symbol\":\"%s\",\"side\":\"%s\",\"volume\":%G,"
        "\"open_price\":%G,\"sl\":%G,\"tp\":%G,\"magic\":%d,"
        "\"comment\":\"%s\",\"open_time\":\"%s\"}",
        OrderTicket(), OrderSymbol(), side, OrderLots(),
        OrderOpenPrice(), OrderStopLoss(), OrderTakeProfit(), OrderMagicNumber(),
        JsonEscape(TruncStr(OrderComment(), 64)),
        TimeToISO8601Z_UTC(OrderOpenTime())
      );
      out_count++;                 // ← 正カウント
   }
   j += "]";
   return j;
}

/***********************
 * HTTP
 ***********************/
int HttpPost(string url, string payload, string &resp_body)
{
   const int n = StringLen(payload);   // 文字数（今回のJSONはASCIIなのでバイト数=文字数）
   char data[];
   ArrayResize(data, n);               // ぴったり確保（終端ヌル用の+1は作らない）

   // 第4引数に n を指定 => ちょうど n 文字だけコピー（終端ヌルはコピーしない）
   int copied = StringToCharArray(payload, data, 0, n, CP_UTF8);

   // --- 診断：ゼロバイト混入検査（あるとNG）
   int zero_count = 0, last_zero_idx = -1;
   for(int i=0;i<ArraySize(data);i++){
      if(data[i]==0){ zero_count++; last_zero_idx=i; }
   }
   if(zero_count>0){
      Print("WARN: payload has ", zero_count, " zero-bytes; last at idx=", last_zero_idx,
            " n=", n, " copied=", copied, " url=", url);
   }

   string headers =
      "Content-Type: application/json\r\n"
      "Authorization: Api-Key " + API_KEY + "\r\n";

   char result[]; string resp_headers;
   int status = WebRequest("POST", url, headers, 10000, data, result, resp_headers);
   if(status == -1){
      Print("WebRequest failed err=", GetLastError(), " url=", url);
      ResetLastError();
      resp_body = "";
      return 0;
   }
   resp_body = CharArrayToString(result, 0, -1, CP_UTF8);
   return status;
}

/***********************
 * UTIL
 ***********************/
// サーバ時刻→UTCに補正し、ISO8601 'YYYY-MM-DDTHH:MM:SSZ' を返す
string TimeToISO8601Z_UTC(datetime t_server)
{
   // サーバ時刻とGMTのオフセット秒を計算
   int offset = (int)(TimeCurrent() - TimeGMT()); // TimeCurrent(): サーバ現在時刻, TimeGMT(): UTC現在時刻
   datetime t_utc = t_server - offset;

   MqlDateTime dt;
   TimeToStruct(t_utc, dt);
   return StringFormat("%04d-%02d-%02dT%02d:%02d:%02dZ", dt.year, dt.mon, dt.day, dt.hour, dt.min, dt.sec);
}

// 文字列のJSONエスケープ（\ と " のみ）
string JsonEscape(string s)
{
   s = StringReplace(s,"\\","\\\\");
   s = StringReplace(s,"\"","\\\"");
   return s;
}

// 指定長でトリム（UTF-16のサロゲート考慮は不要前提）
string TruncStr(string s, int maxlen)
{
   if(StringLen(s) > maxlen) return StringSubstr(s, 0, maxlen);
   return s;
}

// 可視ログ用：["...","..."] の要素数を軽く数える（厳密パースはしない）
int CountItemsInArrayJson(string arrJson)
{
   // 空配列 "[]" → 0
   int len = StringLen(arrJson);
   if(len < 2) return 0;
   // 優しめカウント：',' の数 + 1。ただし "[]" は 0
   int commas = 0;
   for(int i=0;i<len;i++)
      if(StringGetCharacter(arrJson, i) == ',') commas++;
   // 内容が "[]"
   if(commas==0)
   {
      // 先頭が '[' で末尾が ']' かつ中身が空白のみなら 0
      string inner = StringSubstr(arrJson, 1, len-2);
      StringTrimLeft(inner); StringTrimRight(inner);
      if(StringLen(inner)==0) return 0;
   }
   return commas + 1;
}
