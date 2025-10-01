import re
from pathlib import Path

files = [
    'tam-nhin-su-menh.html',
    'dieu-khoan-dieu-kien.html',
    'chinh-sach-doi-tra.html',
    'chinh-sach-bao-mat-thong-tin-ca-nhan.html',
    'chinh-sach-bao-mat-thong-tin-thanh-toan.html',
    'chinh-sach-bao-hanh.html',
    'tro-thanh-nha-phan-phoi.html'
]

step_pattern = re.compile(r'-\s*B\u01b0\u1edbc\s*\d+\s*:', re.IGNORECASE)
para_pattern = re.compile(r'<p>(.*?)</p>', re.S)

normalize = lambda text: ' '.join(text.split())

for file in files:
    path = Path(file)
    html = path.read_text(encoding='utf-8')
    def convert_paragraph(match):
        original = match.group(0)
        content = match.group(1)
        if step_pattern.search(content) is None:
            return original
        trimmed = content.strip()
        matches = list(step_pattern.finditer(trimmed))
        if not matches:
            return original
        prefix = trimmed[:matches[0].start()].strip()
        items = []
        for idx, m in enumerate(matches):
            start = m.start()
            end = matches[idx + 1].start() if idx + 1 < len(matches) else len(trimmed)
            chunk = trimmed[start:end].strip()
            chunk = chunk.lstrip('- ').strip()
            items.append(chunk)
        suffix = ''
        if items:
            last_chunk = items[-1]
            parts = [part.strip() for part in re.split(r'\n\s*\n', last_chunk) if part.strip()]
            if parts:
                items[-1] = parts[0]
                if len(parts) > 1:
                    suffix = '\n\n'.join(parts[1:])
        items = [normalize(item) for item in items]
        suffix = normalize(suffix) if suffix else ''
        parts_html = []
        if prefix:
            parts_html.append(f'<p>{normalize(prefix)}</p>')
        parts_html.append('<ul>')
        for item in items:
            parts_html.append(f'    <li>{item}</li>')
        parts_html.append('</ul>')
        if suffix:
            parts_html.append(f'<p>{suffix}</p>')
        return '\n'.join(parts_html)
    html_new = para_pattern.sub(convert_paragraph, html)
    path.write_text(html_new, encoding='utf-8')
