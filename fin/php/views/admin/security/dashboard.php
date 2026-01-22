<?php
/**
 * Security Dashboard View
 * لوحة مراقبة الأمان
 */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">لوحة المراقبة الأمنية</h1>
            <p class="text-gray-600">مراقبة وتحليل الأنشطة الأمنية</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">آخر تحديث: <span id="lastUpdate"><?= date('H:i:s') ?></span></span>
            <button onclick="refreshDashboard()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i class="fas fa-sync-alt ml-2"></i>
                تحديث
            </button>
        </div>
    </div>

    <!-- Security Score -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg opacity-90">درجة الأمان</h3>
                <div class="flex items-baseline gap-2 mt-2">
                    <span id="securityScore" class="text-5xl font-bold">--</span>
                    <span class="text-2xl opacity-75">/ 100</span>
                </div>
                <p id="securityStatus" class="mt-2 opacity-90">جاري التحميل...</p>
            </div>
            <div class="text-8xl opacity-20">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- إجمالي محاولات الدخول -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">محاولات الدخول اليوم</p>
                    <p id="totalAttempts" class="text-3xl font-bold text-gray-900 mt-1">--</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-sign-in-alt text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span id="successAttempts" class="text-green-600">-- ناجحة</span>
                <span class="mx-2 text-gray-300">|</span>
                <span id="failedAttempts" class="text-red-600">-- فاشلة</span>
            </div>
        </div>

        <!-- الهجمات المكتشفة -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">الهجمات المكتشفة</p>
                    <p id="attacksDetected" class="text-3xl font-bold text-gray-900 mt-1">--</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-500">Brute Force, XSS, SQL Injection</p>
        </div>

        <!-- الحسابات المقفلة -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">الحسابات المقفلة</p>
                    <p id="lockedAccounts" class="text-3xl font-bold text-gray-900 mt-1">--</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-lock text-yellow-600 text-xl"></i>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-500">بسبب محاولات فاشلة متعددة</p>
        </div>

        <!-- المستخدمين النشطين -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">المستخدمين النشطين</p>
                    <p id="activeUsers" class="text-3xl font-bold text-gray-900 mt-1">--</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-500">آخر 24 ساعة</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Security Events Log -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">سجل الأحداث الأمنية</h3>
                    <div class="flex items-center gap-2">
                        <select id="eventFilter" onchange="filterEvents()" class="text-sm border-gray-200 rounded-lg">
                            <option value="">جميع الأحداث</option>
                            <option value="high">خطورة عالية</option>
                            <option value="medium">خطورة متوسطة</option>
                            <option value="low">خطورة منخفضة</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto">
                <div id="eventsContainer" class="space-y-4">
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>جاري تحميل الأحداث...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suspicious IPs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">عناوين IP المشبوهة</h3>
            </div>
            <div class="p-6">
                <div id="suspiciousIPs" class="space-y-3">
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>جاري التحميل...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">إحصائيات الأسبوع</h3>
        <div id="weeklyChart" class="h-64">
            <!-- Chart will be rendered here -->
        </div>
    </div>

    <!-- Threats Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">التهديدات المكتشفة</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">النوع</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المستخدم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">IP</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الوصف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الوقت</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الخطورة</th>
                    </tr>
                </thead>
                <tbody id="threatsTableBody" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                            <p>جاري التحميل...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// تحميل البيانات عند فتح الصفحة
document.addEventListener('DOMContentLoaded', function() {
    loadSecuritySummary();
    loadSecurityEvents();
    loadThreats();
});

// تحميل الملخص الأمني
async function loadSecuritySummary() {
    try {
        const response = await fetch('/api/security.php?action=summary', {
            headers: {
                'Authorization': 'Bearer ' + getToken()
            }
        });
        const data = await response.json();
        
        if (data.success) {
            const summary = data.data;
            
            // تحديث الإحصائيات
            document.getElementById('totalAttempts').textContent = summary.today.total_login_attempts;
            document.getElementById('successAttempts').textContent = summary.today.successful_attempts + ' ناجحة';
            document.getElementById('failedAttempts').textContent = summary.today.failed_attempts + ' فاشلة';
            document.getElementById('attacksDetected').textContent = summary.today.attacks_detected;
            document.getElementById('lockedAccounts').textContent = summary.today.locked_accounts;
            document.getElementById('activeUsers').textContent = summary.today.active_users;
            
            // تحديث درجة الأمان
            document.getElementById('securityScore').textContent = summary.security_score.score;
            document.getElementById('securityStatus').textContent = 'الحالة: ' + summary.security_score.status_label;
            
            // تحديث IPs المشبوهة
            renderSuspiciousIPs(summary.suspicious_ips);
            
            // رسم الرسم البياني
            renderWeeklyChart(summary.weekly_stats);
        }
    } catch (error) {
        console.error('Error loading security summary:', error);
    }
}

// تحميل الأحداث الأمنية
async function loadSecurityEvents() {
    try {
        const filter = document.getElementById('eventFilter').value;
        let url = '/api/security.php?action=events&per_page=10';
        if (filter) {
            url += '&level=' + filter;
        }
        
        const response = await fetch(url, {
            headers: {
                'Authorization': 'Bearer ' + getToken()
            }
        });
        const data = await response.json();
        
        if (data.success) {
            renderEvents(data.data);
        }
    } catch (error) {
        console.error('Error loading events:', error);
    }
}

// عرض الأحداث
function renderEvents(events) {
    const container = document.getElementById('eventsContainer');
    
    if (events.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">لا توجد أحداث</p>';
        return;
    }
    
    container.innerHTML = events.map(event => `
        <div class="flex items-start gap-4 p-4 rounded-lg ${getSeverityBgClass(event.severity)}">
            <div class="w-10 h-10 rounded-full ${getSeverityIconBgClass(event.severity)} flex items-center justify-center">
                <i class="fas ${getEventIcon(event.action_type)} ${getSeverityTextClass(event.severity)}"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <h4 class="font-medium text-gray-900">${event.action_type}</h4>
                    <span class="px-2 py-1 text-xs rounded-full ${getSeverityBadgeClass(event.severity)}">
                        ${event.severity_label}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mt-1">${event.description || 'لا يوجد وصف'}</p>
                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                    <span><i class="fas fa-user ml-1"></i>${event.user_name || 'زائر'}</span>
                    <span><i class="fas fa-globe ml-1"></i>${event.ip_address}</span>
                    <span><i class="fas fa-clock ml-1"></i>${formatDate(event.created_at)}</span>
                </div>
            </div>
        </div>
    `).join('');
}

// تحميل التهديدات
async function loadThreats() {
    try {
        const response = await fetch('/api/security.php?action=threats', {
            headers: {
                'Authorization': 'Bearer ' + getToken()
            }
        });
        const data = await response.json();
        
        if (data.success) {
            renderThreats(data.data);
        }
    } catch (error) {
        console.error('Error loading threats:', error);
    }
}

// عرض التهديدات
function renderThreats(threats) {
    const tbody = document.getElementById('threatsTableBody');
    
    if (threats.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">لا توجد تهديدات مكتشفة ✓</td></tr>';
        return;
    }
    
    tbody.innerHTML = threats.map(threat => `
        <tr class="hover:bg-red-50">
            <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded">
                    ${threat.threat_type_label}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">${threat.user_name || 'زائر'}</td>
            <td class="px-6 py-4 text-sm text-gray-500 font-mono">${threat.ip_address}</td>
            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">${threat.description || '-'}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${formatDate(threat.created_at)}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-bold bg-red-600 text-white rounded">
                    ${threat.severity_label}
                </span>
            </td>
        </tr>
    `).join('');
}

// عرض IPs المشبوهة
function renderSuspiciousIPs(ips) {
    const container = document.getElementById('suspiciousIPs');
    
    if (ips.length === 0) {
        container.innerHTML = '<p class="text-center text-green-600 py-4"><i class="fas fa-check-circle ml-2"></i>لا توجد عناوين مشبوهة</p>';
        return;
    }
    
    container.innerHTML = ips.map(ip => `
        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
            <span class="font-mono text-sm text-gray-900">${ip.ip_address}</span>
            <span class="px-2 py-1 text-xs font-bold bg-red-600 text-white rounded">${ip.attempts} محاولة</span>
        </div>
    `).join('');
}

// رسم الرسم البياني الأسبوعي
function renderWeeklyChart(stats) {
    const container = document.getElementById('weeklyChart');
    
    if (stats.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">لا توجد بيانات كافية</p>';
        return;
    }
    
    // Simple bar chart using CSS
    const maxValue = Math.max(...stats.map(s => s.successful + s.failed)) || 1;
    
    container.innerHTML = `
        <div class="flex items-end justify-around h-full gap-4">
            ${stats.map(stat => `
                <div class="flex flex-col items-center flex-1">
                    <div class="flex items-end gap-1 h-40 w-full">
                        <div class="flex-1 bg-green-500 rounded-t" style="height: ${(stat.successful / maxValue) * 100}%"></div>
                        <div class="flex-1 bg-red-500 rounded-t" style="height: ${(stat.failed / maxValue) * 100}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">${formatShortDate(stat.date)}</p>
                </div>
            `).join('')}
        </div>
        <div class="flex items-center justify-center gap-6 mt-4">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-green-500 rounded"></div>
                <span class="text-sm text-gray-600">ناجحة</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-red-500 rounded"></div>
                <span class="text-sm text-gray-600">فاشلة</span>
            </div>
        </div>
    `;
}

// Helper functions
function getToken() {
    return localStorage.getItem('admin_token') || '';
}

function getSeverityBgClass(severity) {
    switch(severity) {
        case 'high': return 'bg-red-50 border-r-4 border-red-500';
        case 'medium': return 'bg-yellow-50 border-r-4 border-yellow-500';
        default: return 'bg-gray-50 border-r-4 border-gray-300';
    }
}

function getSeverityIconBgClass(severity) {
    switch(severity) {
        case 'high': return 'bg-red-100';
        case 'medium': return 'bg-yellow-100';
        default: return 'bg-gray-100';
    }
}

function getSeverityTextClass(severity) {
    switch(severity) {
        case 'high': return 'text-red-600';
        case 'medium': return 'text-yellow-600';
        default: return 'text-gray-600';
    }
}

function getSeverityBadgeClass(severity) {
    switch(severity) {
        case 'high': return 'bg-red-100 text-red-700';
        case 'medium': return 'bg-yellow-100 text-yellow-700';
        default: return 'bg-gray-100 text-gray-700';
    }
}

function getEventIcon(type) {
    switch(type) {
        case 'login_success': return 'fa-sign-in-alt';
        case 'login_failed': return 'fa-times-circle';
        case 'logout': return 'fa-sign-out-alt';
        case 'brute_force': return 'fa-skull-crossbones';
        case 'xss_attempt': return 'fa-code';
        case 'sql_injection': return 'fa-database';
        default: return 'fa-shield-alt';
    }
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleString('ar-SA');
}

function formatShortDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('ar-SA', { weekday: 'short' });
}

function filterEvents() {
    loadSecurityEvents();
}

function refreshDashboard() {
    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString('ar-SA');
    loadSecuritySummary();
    loadSecurityEvents();
    loadThreats();
}

// تحديث تلقائي كل 30 ثانية
setInterval(refreshDashboard, 30000);
</script>
