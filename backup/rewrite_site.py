from pathlib import Path

html = """<!DOCTYPE html>
<html lang=\"vi\">
<head>
    <meta charset=\"UTF-8\" />
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />
    <meta name=\"description\" content=\"Giải pháp lắp đặt pin năng lượng mặt trời cho hộ gia đình và doanh nghiệp.\" />
    <title>HC Eco System</title>
    <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
    <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>
    <link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap\" rel=\"stylesheet\">
    <link rel=\"stylesheet\" href=\"assets/css/styles.css\" />
    <link rel=\"icon\" type=\"image/jpeg\" href=\"assets/img/logo.jpg\" />
</head>
<body>
    <header class=\"topbar\">
        <div class=\"container topbar-layout\">
            <div class=\"branding\">
                <img class=\"logo-image\" src=\"assets/img/logo.jpg\" alt=\"Logo HC Eco System\" />
                <div class=\"brand-copy\">
                    <div class=\"logo\">HC<span>ECO SYSTEM</span></div>
                    <p>Giải pháp bền vững cho năng lượng tương lai</p>
                </div>
            </div>
            <form class=\"header-search\" action=\"#\" role=\"search\">
                <label for=\"top-search\" class=\"sr-only\">Tìm kiếm</label>
                <input id=\"top-search\" type=\"search\" name=\"q\" placeholder=\"Tìm kiếm giải pháp năng lượng...\" autocomplete=\"off\" />
                <button type=\"submit\">Tìm</button>
            </form>
            <div class=\"topbar-actions\">
                <nav class=\"nav\">
                    <a href=\"#solutions\">Giải pháp</a>
                    <a href=\"#benefits\">Ưu điểm</a>
                    <a href=\"#process\">Quy trình</a>
                    <a href=\"#contact\">Liên hệ</a>
                </nav>
                <a class=\"cta\" href=\"#contact\">Nhận tư vấn</a>
            </div>
        </div>
    </header>

    <aside class=\"quick-contact\" aria-label=\"Liên hệ nhanh\">
        <a class=\"contact-item contact-item--fanpage\" href=\"https://www.facebook.com/hc.ecosystem\" target=\"_blank\" rel=\"noopener\">
            <span class=\"icon\" aria-hidden=\"true\">
                <svg viewBox=\"0 0 24 24\" role=\"img\" focusable=\"false\">
                    <path d=\"M15.6 3H18V0h-2.6C11.8 0 10 2.1 10 5.2V8H7v3h3v9h3v-9h3l.5-3H13V5.4c0-1.2.4-2.4 2.6-2.4z\" fill=\"currentColor\" />
                </svg>
            </span>
            <span class=\"label\">
                <strong>Fanpage</strong>
                <span>HC Eco System</span>
            </span>
        </a>
        <a class=\"contact-item contact-item--zalo\" href=\"https://zalo.me/0977247393\" target=\"_blank\" rel=\"noopener\">
            <span class=\"icon\" aria-hidden=\"true\">
                <svg viewBox=\"0 0 24 24\" role=\"img\" focusable=\"false\">
                    <path d=\"M18.5 3H5.5A2.5 2.5 0 003 5.5v9A2.5 2.5 0 005.5 17H9v2.8a1 1 0 001.6.8l4.3-3.6h3.6a2.5 2.5 0 002.5-2.5v-9A2.5 2.5 0 0018.5 3z\" fill=\"currentColor\" />
                </svg>
            </span>
            <span class=\"label\">
                <strong>Zalo</strong>
                <span>0977 247 393</span>
            </span>
        </a>
        <a class=\"contact-item contact-item--tiktok\" href=\"https://www.tiktok.com/@hcecosystem\" target=\"_blank\" rel=\"noopener\">
            <span class=\"icon\" aria-hidden=\"true\">
                <svg viewBox=\"0 0 24 24\" role=\"img\" focusable=\"false\">
                    <path d=\"M16.5 2h3a4.5 4.5 0 004.5 4.5v3a7.5 7.5 0 01-4.5-1.5V17a6.5 6.5 0 11-6.5-6.5v3a3.5 3.5 0 103.5 3.5V2z\" fill=\"currentColor\" />
                </svg>
            </span>
            <span class=\"label\">
                <strong>TikTok</strong>
                <span>@hcecosystem</span>
            </span>
        </a>
        <a class=\"contact-item contact-item--youtube\" href=\"https://www.youtube.com/@hcecosystem\" target=\"_blank\" rel=\"noopener\">
            <span class=\"icon\" aria-hidden=\"true\">
                <svg viewBox=\"0 0 24 24\" role=\"img\" focusable=\"false\">
                    <path d=\"M21.8 8.2a2.5 2.5 0 00-1.8-1.8C18 6 12 6 12 6s-6 0-8 0.4A2.5 2.5 0 002.2 8.2 26.5 26.5 0 002 12a26.5 26.5 0 00.2 3.8 2.5 2.5 0 001.8 1.8C6 18 12 18 12 18s6 0 8-0.4a2.5 2.5 0 001.8-1.8c.2-1.2.2-2.6.2-3.8a26.5 26.5 0 00-.2-3.8zM10 15V9l5 3-5 3z\" fill=\"currentColor\" />
                </svg>
            </span>
            <span class=\"label\">
                <strong>YouTube</strong>
                <span>@hcecosystem</span>
            </span>
        </a>
        <a class=\"contact-item contact-item--phone\" href=\"tel:0977247393\">
            <span class=\"icon\" aria-hidden=\"true\">
                <svg viewBox=\"0 0 24 24\" role=\"img\" focusable=\"false\">
                    <path d=\"M6.2 2A2.2 2.2 0 004 4.3c0 9.1 7.6 16.7 16.7 16.7A2.2 2.2 0 0023 18.8v-3.4a1.1 1.1 0 00-.9-1.1l-4.8-1a1.1 1.1 0 00-1 .4l-1.8 2.2a12.9 12.9 0 01-5.4-5.4l2.2-1.8a1.1 1.1 0 00.4-1l-1-4.8A1.1 1.1 0 009.2 2H6.2z\" fill=\"currentColor\" />
                </svg>
            </span>
            <span class=\"label\">
                <strong>Hotline</strong>
                <span>0977 247 393</span>
            </span>
        </a>
    </aside>

    <main>
        <section class=\"hero\">
            <div class=\"container\">
                <div class=\"hero-content\">
                    <span class=\"eyebrow\">Lắp đặt nhanh chóng · Hiệu suất tối ưu</span>
                    <h1>Tiết kiệm chi phí điện với hệ thống pin năng lượng mặt trời chuẩn quốc tế</h1>
                    <p>Chúng tôi thiết kế và thi công trọn gói hệ thống NLMT phù hợp cho mái nhà hộ gia đình, nhà xưởng và tòa nhà thương mại, đảm bảo giảm tới 60% hóa đơn tiền điện.</p>
                    <div class=\"hero-actions\">
                        <a class=\"btn primary\" href=\"#contact\">Đăng ký khảo sát miễn phí</a>
                        <a class=\"btn secondary\" href=\"#solutions\">Xem gói giải pháp</a>
                    </div>
                </div>
                <div class=\"hero-card\">
                    <div class=\"stats\">
                        <article>
                            <h3>250+</h3>
                            <p>Dự án đã triển khai</p>
                        </article>
                        <article>
                            <h3>98%</h3>
                            <p>Khách hàng hài lòng</p>
                        </article>
                    </div>
                    <ul class=\"highlights\">
                        <li>Thiết kế tối ưu theo mái nhà</li>
                        <li>Bảo hành hệ thống đến 12 năm</li>
                        <li>Theo dõi sản lượng trực tuyến 24/7</li>
                    </ul>
                </div>
            </div>
        </section>

        <section id=\"solutions\" class=\"solutions\">
            <div class=\"container\">
                <header>
                    <h2>Giải pháp phù hợp cho mọi nhu cầu</h2>
                    <p>Chọn gói lắp đặt đáp ứng nhu cầu sử dụng điện và diện tích mái của bạn.</p>
                </header>
                <div class=\"grid\">
                    <article class=\"card\">
                        <h3>Gói Gia Đình</h3>
                        <p>Hệ thống 3-5 kWp, phù hợp nhà phố, biệt thự. Thiết bị quốc tế, bảo hiểm sản lượng.</p>
                        <ul>
                            <li>Bộ inverter hybrid</li>
                            <li>Giám sát qua ứng dụng di động</li>
                            <li>Bảo hành vật tư 12 năm</li>
                        </ul>
                    </article>
                    <article class=\"card\">
                        <h3>Gói Doanh Nghiệp</h3>
                        <p>Lắp đặt 20-200 kWp, tối ưu cho nhà xưởng, trang trại. Hỗ trợ thủ tục đấu nối.</p>
                        <ul>
                            <li>Phân tích ROI chi tiết</li>
                            <li>Hệ khung nhôm định hình cao cấp</li>
                            <li>Chăm sóc vận hành định kỳ</li>
                        </ul>
                    </article>
                    <article class=\"card\">
                        <h3>Gói Tiết Kiệm</h3>
                        <p>Giải pháp NLMT hòa lưới bán phần, phù hợp ngân sách tối ưu nhưng vẫn hiệu quả.</p>
                        <ul>
                            <li>Linh kiện đạt tiêu chuẩn IEC</li>
                            <li>Lắp đặt trong 3 ngày</li>
                            <li>Hỗ trợ trả góp 0%</li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        <section id=\"benefits\" class=\"benefits\">
            <div class=\"container\">
                <h2>Vì sao chọn HC Eco System?</h2>
                <div class=\"grid\">
                    <article>
                        <h3>Kỹ thuật viên chứng nhận</h3>
                        <p>Đội ngũ được đào tạo bởi các hãng dẫn đầu như Longi, Sungrow, đảm bảo thi công chuẩn quốc tế.</p>
                    </article>
                    <article>
                        <h3>Giải pháp tùy chỉnh</h3>
                        <p>Phân tích nhu cầu điện năng, thiết kế theo không gian mái để tối ưu sản lượng.</p>
                    </article>
                    <article>
                        <h3>Hỗ trợ vận hành 24/7</h3>
                        <p>Đội ngũ kỹ thuật trực hotline, hỗ trợ từ xa và bảo trì định kỳ trong suốt vòng đời hệ thống.</p>
                    </article>
                    <article>
                        <h3>Đảm bảo tài chính</h3>
                        <p>Cung cấp giải pháp trả góp linh hoạt, hỗ trợ vay vốn xanh lãi suất thấp từ ngân hàng đối tác.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id=\"process\" class=\"process\">
            <div class=\"container\">
                <h2>Quy trình triển khai trong 5 bước</h2>
                <div class=\"steps\">
                    <article>
                        <span class=\"step-number\">01</span>
                        <h3>Khảo sát &amp; đo đạc</h3>
                        <p>Kỹ sư đến trực tiếp để khảo sát mái, đo bức xạ và đánh giá hạ tầng điện.</p>
                    </article>
                    <article>
                        <span class=\"step-number\">02</span>
                        <h3>Thiết kế mô phỏng</h3>
                        <p>Đưa ra sơ đồ lắp đặt 3D và dự báo sản lượng, thời gian hoàn vốn.</p>
                    </article>
                    <article>
                        <span class=\"step-number\">03</span>
                        <h3>Ký kết &amp; thi công</h3>
                        <p>Chuẩn hóa hợp đồng, triển khai thi công an toàn, hoàn tất trong 3-7 ngày.</p>
                    </article>
                    <article>
                        <span class=\"step-number\">04</span>
                        <h3>Nghiệm thu &amp; đấu nối</h3>
                        <p>Kết nối hệ thống với lưới điện, bàn giao tài liệu hướng dẫn vận hành.</p>
                    </article>
                    <article>
                        <span class=\"step-number\">05</span>
                        <h3>Theo dõi &amp; bảo trì</h3>
                        <p>Giám sát sản lượng từ xa, vệ sinh tấm pin định kỳ, phản hồi sự cố trong 4h.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class=\"cta-banner\">
            <div class=\"container\">
                <div class=\"content\">
                    <h2>Tối ưu hóa chi phí điện ngay hôm nay</h2>
                    <p>Đăng ký khảo sát miễn phí để được tư vấn giải pháp phù hợp nhất cho mái nhà của bạn.</p>
                </div>
                <a class=\"btn primary\" href=\"#contact\">Đặt lịch khảo sát</a>
            </div>
        </section>

        <section id=\"contact\" class=\"contact\">
            <div class=\"container\">
                <div class=\"info\">
                    <h2>Liên hệ HC Eco System</h2>
                    <p>Gọi ngay cho chúng tôi hoặc điền thông tin để được chuyên gia liên hệ trong vòng 24h.</p>
                    <ul>
                        <li><strong>Hotline:</strong> 0977247393</li>
                        <li><strong>Email:</strong> hello@greensun.vn</li>
                        <li><strong>Địa chỉ:</strong> 790 Ngô Quyền, Phường An Hải, Thành Phố Đà Nẵng</li>
                    </ul>
                </div>
                <form class=\"form\">
                    <label>Họ và tên
                        <input type=\"text\" name=\"name\" placeholder=\"Nguyễn Văn A\" required />
                    </label>
                    <label>Số điện thoại
                        <input type=\"tel\" name=\"phone\" placeholder=\"090x xxx xxx\" required />
                    </label>
                    <label>Nhu cầu lắp đặt
                        <select name=\"service\" required>
                            <option value=\"\">Chọn nhu cầu của bạn</option>
                            <option value=\"home\">Gia đình</option>
                            <option value=\"business\">Doanh nghiệp</option>
                            <option value=\"farm\">Trang trại</option>
                        </select>
                    </label>
                    <label>Ghi chú
                        <textarea name=\"message\" rows=\"3\" placeholder=\"Diện tích mái, thời gian thuận tiện...\"></textarea>
                    </label>
                    <button type=\"submit\" class=\"btn primary\">Gửi thông tin</button>
                </form>
            </div>
        </section>
    </main>

    <footer class=\"footer\">
        <div class=\"container\">
            <p>&copy; 2024 HC Eco System. Giữ vững cam kết năng lượng sạch cho mọi nhà.</p>
            <div class=\"links\">
                <a href=\"#\">Chính sách bảo hành</a>
                <a href=\"#\">Tài liệu kỹ thuật</a>
                <a href=\"#\">Tuyển dụng</a>
            </div>
        </div>
    </footer>
</body>
</html>
"""

css = """:root {
    --color-green: #0b8f24;
    --color-green-dark: #06661a;
    --color-yellow: #f2c744;
    --color-white: #ffffff;
    --color-bg: #f6f9f2;
    --color-text: #1f2c1f;
    --color-muted: #5a6d58;
    --max-width: 1120px;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'Inter', Arial, sans-serif;
    color: var(--color-text);
    background: var(--color-bg);
}

... (rest same) ..."""

Path('index.html').write_text(html, encoding='utf-8')
Path('assets/css/styles.css').write_text(css, encoding='utf-8')
