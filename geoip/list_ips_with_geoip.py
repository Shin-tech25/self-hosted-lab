import re
import csv
import geoip2.database

# ファイルパス設定
log_path = "./fail2ban.log"
geoip_db_path = "/usr/share/GeoIP/GeoLite2-Country.mmdb"
output_csv = "banned_ips_with_country.csv"

# Banログの正規表現パターン
ban_pattern = re.compile(r'^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2}).*Ban ([\d\.]+)$')

# 出力データ格納リスト
ban_entries = []

with open(log_path, 'r') as log_file, geoip2.database.Reader(geoip_db_path) as reader:
    for line in log_file:
        match = ban_pattern.match(line)
        if match:
            date, time, ip = match.groups()
            timestamp = f"{date} {time}"
            try:
                response = reader.country(ip)
                country = response.country.name or "Unknown"
            except Exception:
                country = "Unknown"
            ban_entries.append((timestamp, ip, country))

# CSV出力
with open(output_csv, "w", newline='') as csvfile:
    writer = csv.writer(csvfile)
    writer.writerow(["Timestamp", "IP Address", "Country"])
    writer.writerows(ban_entries)

print(f"[✓] 書き出し完了: {output_csv}")
