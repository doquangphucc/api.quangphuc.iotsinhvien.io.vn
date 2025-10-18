/**
 * Slideshow Configuration for Different Pages
 */

// Detect path prefix based on page location
const pathPrefix = window.SLIDESHOW_PATH_PREFIX !== undefined ? window.SLIDESHOW_PATH_PREFIX : '../';

// Homepage Slideshow - Mix of images and videos
const homepageSlides = [
    // Video slides
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 1.mp4`,
        alt: 'Kỹ sư lắp đặt pin mặt trời trên mái nhà'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/solar-installation-vn-rooftop.png`,
        alt: 'Lắp đặt pin mặt trời trên mái nhà Việt Nam'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 4.mp4`,
        alt: 'Hoàng hôn với pin năng lượng mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/solar-panels-sunset.png`,
        alt: 'Tấm pin năng lượng mặt trời lúc hoàng hôn'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 9.mp4`,
        alt: 'Gia đình hạnh phúc với năng lượng mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/family-solar-vietnam.png`,
        alt: 'Gia đình tận hưởng ngôi nhà với năng lượng mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Solar Energy System in a Green Urban Landscape.png`,
        alt: 'Hệ thống năng lượng mặt trời trong đô thị xanh'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 8.mp4`,
        alt: 'Dãy pin năng lượng mặt trời quy mô lớn'
    }
];

// About Us Page Slideshow
const aboutSlides = [
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 2.mp4`,
        alt: 'Tư vấn chuyên nghiệp về năng lượng mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Chuyên gia năng lượng mặt trời đang giới thiệu mô hình giải pháp năng lượng cho một gia đình Việt Nam tại phòng khách ấm cúng của họ.png`,
        alt: 'Chuyên gia tư vấn năng lượng mặt trời'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 3.mp4`,
        alt: 'Kỹ sư kiểm tra hệ thống pin mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Kỹ sư đang kiểm tra cẩn thận các tấm pin năng lượng mặt trời trên mái nhà, với khung cảnh thành phố Việt Nam phía xa. Bầu trời trong xanh và ánh nắng rực rỡ, thể hiện hiệu quả của hệ thống.png`,
        alt: 'Kiểm tra hệ thống năng lượng mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Engineer Consulting with Homeowner about Solar Energy System.png`,
        alt: 'Kỹ sư tư vấn với chủ nhà'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 6.mp4`,
        alt: 'Khách hàng hài lòng với năng lượng mặt trời'
    }
];

// Pricing/Products Page Slideshow
const pricingSlides = [
    {
        type: 'image',
        src: `${pathPrefix}Photo/solar-panel-array.jpg`,
        alt: 'Mảng tấm pin năng lượng mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/solar-panels-blue-sky.jpg`,
        alt: 'Tấm pin dưới bầu trời xanh'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 8.mp4`,
        alt: 'Hệ thống pin mặt trời quy mô lớn'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Close-up of a Solar Panel with Sun Flare.png`,
        alt: 'Cận cảnh tấm pin với ánh sáng mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/modern-solar-panels.jpg`,
        alt: 'Tấm pin hiện đại'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 1.mp4`,
        alt: 'Kỹ sư lắp đặt pin mặt trời trên mái nhà'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/solar-installation-vn-rooftop.png`,
        alt: 'Lắp đặt pin mặt trời trên mái nhà Việt Nam'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 4.mp4`,
        alt: 'Hoàng hôn với pin năng lượng mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/solar-panels-sunset.png`,
        alt: 'Tấm pin năng lượng mặt trời lúc hoàng hôn'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 9.mp4`,
        alt: 'Gia đình hạnh phúc với năng lượng mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Solar Energy System in a Green Urban Landscape.png`,
        alt: 'Hệ thống năng lượng mặt trời trong đô thị xanh'
    }
];

// Survey/Calculator Page Slideshow
const surveySlides = [
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 7.mp4`,
        alt: 'Dòng năng lượng từ mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/residential-solar-home.png`,
        alt: 'Ngôi nhà sử dụng năng lượng mặt trời'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 5.mp4`,
        alt: 'Hệ thống điện sạch từ năng lượng mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Solar Panels on a Modern Vietnamese Villa.png`,
        alt: 'Pin mặt trời trên biệt thự hiện đại'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Một góc nhìn rộng hơn của mái nhà Việt Nam với các tấm pin năng lượng mặt trời được lắp đặt hoàn chỉnh. Ngôi nhà nằm trong khu dân cư xanh mát, thể hiện sự hòa hợp giữa công nghệ và thiên nhiên.png`,
        alt: 'Mái nhà với pin mặt trời hoàn chỉnh'
    }
];

// Projects Page Slideshow
const projectsSlides = [
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 1.mp4`,
        alt: 'Dự án lắp đặt pin mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/solar-farm-field.jpg`,
        alt: 'Trang trại năng lượng mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Technician Installing Solar Panels on a Commercial Building.png`,
        alt: 'Lắp đặt pin trên tòa nhà thương mại'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 8.mp4`,
        alt: 'Dự án quy mô lớn'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/solar-panels-roof-1.jpg`,
        alt: 'Pin mặt trời trên mái nhà'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Solar Streetlights in a Vietnamese City Park.png`,
        alt: 'Đèn đường năng lượng mặt trời'
    }
];

// Installation/Service Page Slideshow
const installationSlides = [
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 3.mp4`,
        alt: 'Quy trình kiểm tra lắp đặt'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/solar-worker-installation.jpg`,
        alt: 'Công nhân lắp đặt pin mặt trời'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Đây là hình ảnh một kỹ sư đang lắp đặt tấm pin năng lượng mặt trời.png`,
        alt: 'Kỹ sư lắp đặt tấm pin'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 1.mp4`,
        alt: 'Quá trình lắp đặt hoàn chỉnh'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Kỹ sư đang kiểm tra hệ thống pin năng lượng mặt trời trên mái nhà dưới ánh nắng chan hòa.png`,
        alt: 'Kiểm tra hệ thống sau lắp đặt'
    }
];

// News Page Slideshow (use homepage slides for variety)
const newsSlides = homepageSlides;

// Contact Page Slideshow
const contactSlides = [
    {
        type: 'image',
        src: `${pathPrefix}Photo/Engineer Consulting with Homeowner about Solar Energy System.png`,
        alt: 'Tư vấn về năng lượng mặt trời'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 2.mp4`,
        alt: 'Tư vấn chuyên nghiệp'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/Nhân viên tư vấn đang trao đổi với khách hàng về lợi ích của năng lượng mặt trời, trên bàn có tài liệu và mô hình tấm pin. Không gian hiện đại, chuyên nghiệp.png`,
        alt: 'Trao đổi với khách hàng'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/solar-farm-field.jpg`,
        alt: 'Trang trại năng lượng mặt trời'
    },
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 6.mp4`,
        alt: 'Khách hàng hài lòng'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/solar-panels-blue-sky.jpg`,
        alt: 'Tấm pin năng lượng mặt trời'
    }
];

// Export configurations based on page
function getSlideshowConfig(pageName) {
    const configs = {
        'home': { slides: homepageSlides, interval: 7000 },
        'about': { slides: aboutSlides, interval: 8000 },
        'pricing': { slides: pricingSlides, interval: 6000 },
        'survey': { slides: surveySlides, interval: 7000 },
        'projects': { slides: projectsSlides, interval: 7000 },
        'installation': { slides: installationSlides, interval: 7000 },
        'news': { slides: newsSlides, interval: 7000 },
        'contact': { slides: contactSlides, interval: 7000 }
    };

    return configs[pageName] || configs['home'];
}

// Auto-detect page and set config
if (typeof window !== 'undefined') {
    // Detect page from URL or data attribute
    const page = document.body.dataset.page || 'home';
    window.slideshowConfig = getSlideshowConfig(page);
}

