/**
 * SwiftCart Corrections Dashboard
 * Main JavaScript File
 */

// Corrections Data
const corrections = [
    { file: "Product.php", issue: "stock → stock_quantity", status: "fixed" },
    { file: "Product.php", issue: "discount_price → sale_price", status: "fixed" },
    { file: "Product.php", issue: "LIMIT/OFFSET مع PDO", status: "fixed" },
    { file: "Cart.php", issue: "جدول cart → cart_items", status: "fixed" },
    { file: "Cart.php", issue: "usage_limit → max_uses", status: "fixed" },
    { file: "Order.php", issue: "shipping_address كـ JSON", status: "fixed" },
    { file: "User.php", issue: "role = 'customer' → 'user'", status: "fixed" },
    { file: "User.php", issue: "phone NOT NULL", status: "fixed" },
    { file: "product.dart", issue: "مطابقة أسماء الحقول", status: "fixed" },
    { file: "order.dart", issue: "shippingAddress JSON parsing", status: "fixed" },
    { file: "api_service.dart", issue: "معالجة الأخطاء", status: "fixed" },
    { file: "cart_provider.dart", issue: "API endpoints", status: "fixed" },
];

// PHP Details Data
const phpDetails = [
    {
        title: "مشكلة LIMIT/OFFSET في PDO",
        description: "PDO لا يقبل متغيرات في LIMIT/OFFSET مباشرة",
        code: `// ❌ خطأ
$params[] = $perPage;
$stmt->execute($params);

// ✅ صحيح  
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);`,
        type: "error"
    },
    {
        title: "أسماء الأعمدة غير متطابقة",
        description: "Schema مختلف عن الكود",
        code: `stock → stock_quantity
discount_price → sale_price
views → views_count
cart → cart_items`,
        type: "warning"
    },
    {
        title: "حقل phone مطلوب",
        description: "Schema يتطلب phone NOT NULL",
        code: `// ✅ إضافة قيمة افتراضية
$values[] = $data['phone'] ?? '0000000000';`,
        type: "success"
    },
    {
        title: "ملفات مفقودة",
        description: "ملفات ضرورية غير موجودة",
        code: `+ models/Category.php (جديد)
+ api/categories.php (جديد)
+ utils/FileUpload.php (جديد)
+ جدول notifications (جديد)`,
        type: "success"
    }
];

// Flutter Details Data
const flutterDetails = [
    {
        title: "نموذج Product",
        description: "مطابقة الحقول مع PHP Backend",
        code: `stockQuantity (not stock)
salePrice (not discountPrice)
viewsCount (not views)`,
        type: "success"
    },
    {
        title: "API Service",
        description: "تحسين معالجة الأخطاء والتوكن",
        code: `headers['Authorization'] = 'Bearer \$token';
// معالجة timeout و connection errors`,
        type: "success"
    },
    {
        title: "Products Provider",
        description: "تصحيح endpoints",
        code: `products.php?action=list&page=1
products.php?action=featured
categories.php?action=list`,
        type: "success"
    },
    {
        title: "Order Model",
        description: "تحويل shipping_address لـ JSON",
        code: `shippingAddress = jsonDecode(json['shipping_address'])`,
        type: "success"
    }
];

// File icon SVG
const fileIconSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
    <polyline points="14 2 14 8 20 8"/>
</svg>`;

// Render Corrections List
function renderCorrections() {
    const container = document.getElementById('corrections-list');
    if (!container) return;
    
    container.innerHTML = corrections.map((item, index) => `
        <div class="correction-item" style="animation-delay: ${index * 0.05}s">
            <div class="correction-info">
                ${fileIconSVG}
                <span class="file-name">${item.file}</span>
                <span class="separator">-</span>
                <span class="issue-text">${item.issue}</span>
            </div>
            <span class="badge">✓ تم الإصلاح</span>
        </div>
    `).join('');
}

// Render Details Cards
function renderDetails(containerId, data) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = data.map((item, index) => `
        <div class="detail-card ${item.type}" style="animation-delay: ${index * 0.1}s">
            <h4>${item.title}</h4>
            <p>${item.description}</p>
            <code>${item.code}</code>
        </div>
    `).join('');
}

// Initialize on DOM Load
document.addEventListener('DOMContentLoaded', function() {
    renderCorrections();
    renderDetails('php-details', phpDetails);
    renderDetails('flutter-details', flutterDetails);
    
    // Add scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.stat-card, .folder-card, .details-section').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });
});

// Console message
console.log('%c SwiftCart Corrections Dashboard ', 'background: linear-gradient(135deg, #3b82f6, #a855f7); color: white; font-size: 16px; padding: 10px; border-radius: 5px;');
console.log('تم تحميل لوحة تصحيحات SwiftCart بنجاح ✓');
