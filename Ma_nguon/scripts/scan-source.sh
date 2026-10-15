#!/usr/bin/env bash
set -u

if [[ $# -lt 1 ]]; then
    echo "Lỗi: Thiếu đường dẫn thư mục." >&2
    exit 2
fi

target_dir="$1"

if [[ ! -d "$target_dir" ]]; then
    echo "Lỗi: Thư mục không tồn tại: $target_dir" >&2
    exit 2
fi

total=0
pass=0
fail=0

while IFS= read -r -d '' file; do
    total=$((total + 1))
    if php -l "$file" >/dev/null 2>&1; then
        pass=$((pass + 1))
    else
        fail=$((fail + 1))
    fi
done < <(find "$target_dir" -type f -name "*.php" -print0)

if [[ "$total" -eq 0 ]]; then
    echo "Lỗi: Không có tệp PHP nào trong thư mục." >&2
    exit 2
fi

echo "TOTAL=$total PASS=$pass FAIL=$fail"

if [[ "$fail" -gt 0 ]]; then
    exit 1
else
    exit 0
fi
