import re

input_file = "requirements.txt"
output_file = "requirements_clean.txt"

pattern = re.compile(r"^(?P<name>[a-zA-Z0-9_\-]+)\s+@ file://.*/(?P<filename>.+?)-(?P<version>[0-9][^-/]*)-(?:.+)?\.whl")

with open(input_file, "r") as f:
    lines = f.readlines()

converted = []
for line in lines:
    line = line.strip()
    if not line or line.startswith("#"):
        converted.append(line)
        continue

    match = pattern.match(line)
    if match:
        pkg = match.group("name")
        ver = match.group("version")
        converted.append(f"{pkg}=={ver}")
    else:
        converted.append(line)

with open(output_file, "w") as f:
    f.write("\n".join(converted))

print(f"✅ Converted requirements written to {output_file}")