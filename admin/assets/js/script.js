/**
 * Project: Labour Management System
 * Description: SPA Logic with Clean URLs, Role-Based Security & Reload Persistence
 * Updated: Sidebar Accordion Logic Added
 */

// 1. Toastr Configurations
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3000",
};

// 2. Sidebar Toggle & Accordion Logic
document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    // Sidebar Toggle (Mobile/Desktop)
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }

    // --- SIDEBAR ACCORDION LOGIC ---
    // Tamam un links ko pakarta hai jo dropdown (collapse) trigger karte hain
    const dropdownLinks = document.querySelectorAll('#sidebar .nav-link[data-bs-toggle="collapse"]');

    dropdownLinks.forEach(link => {
        link.addEventListener('click', function () {
            const targetId = this.getAttribute('data-bs-target');
            const allCollapses = document.querySelectorAll('#sidebar .collapse');

            allCollapses.forEach(collapse => {
                // Agar ye wo element nahi hai jis par click kiya gaya, to isay band kar dein
                if ('#' + collapse.id !== targetId) {
                    const bsCollapse = bootstrap.Collapse.getInstance(collapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });
    });
});

/**
 * 3. Main Content Loader (Upgraded with Role Security & Persistence)
 */
// function loadContent(fileName, e = null, callback = null) {
//     if (e && typeof e.preventDefault === 'function') {
//         e.preventDefault();
//     }

//     if (!fileName || fileName === "#" || fileName === "") return;

//     const newUrl = window.location.pathname + "?p=" + fileName;
//     window.history.pushState({ path: fileName }, '', newUrl);

//     localStorage.setItem('last_active_page', fileName);

//     const mainContent = document.getElementById('main-content');
//     if (!mainContent) return;

//     let contentArea = mainContent.querySelector('.container-fluid') || mainContent;
//     contentArea.innerHTML = `
//         <div class="text-center py-5">
//             <div class="spinner-border text-primary" role="status"></div>
//             <p class="mt-2 text-muted">Loading, please wait...</p>
//         </div>`;
//     fetch(fileName)
//         .then(response => {
//             if (response.redirected) {
//                 window.location.href = response.url;
//                 return;
//             }
//             if (response.status === 401) {
//                 window.location.href = "../auth/login.php?error=unauthorized";
//                 return;
//             }
//             if (!response.ok) throw new Error('Network response error: ' + response.status);
//             return response.text();
//         })
//         .then(data => {
//             if (data.includes('id="loginForm"') || data.includes('Welcome Back')) {
//                 window.location.href = "../auth/login.php";
//                 return;
//             }

//             contentArea.innerHTML = data;

//             // 5. Re-initialize Plugins
//             if (fileName.toLowerCase().includes('dashboard')) {
//                 let attempts = 0;
//                 const checkExist = setInterval(function () {
//                     const canvas = document.getElementById('fuelTrendChart');
//                     if (canvas) {
//                         initializeDashboardChart();
//                         clearInterval(checkExist);
//                     }
//                     if (++attempts > 15) clearInterval(checkExist);
//                 }, 150);
//             }

//             if (window.jQuery && $.fn.select2) {
//                 $('.select2-init, .select2-dist').select2({ width: '100%' });
//             }

//             if (typeof callback === 'function') callback();
//         })
//         .catch(error => {
//             contentArea.innerHTML = `<div class="alert alert-danger m-3">Error loading page.</div>`;
//         });
// }

function loadContent(fileName, e = null) {
    if (e) e.preventDefault();
    
    const mainContent = document.getElementById('main-content');

    fetch(fileName)
        .then(response => response.text())
        .then(html => {
            // Purani dynamic scripts ko saf karein
            $(".dynamic-script").remove();

            // Content load karein
            mainContent.innerHTML = html;

            // Page ke andar maujood script ka path dhoondein
            const scriptTag = mainContent.querySelector('[data-page-script]');
            
            if (scriptTag) {
                const scriptPath = scriptTag.getAttribute('data-page-script');
                const newScript = document.createElement('script');
                newScript.src = scriptPath + "?v=" + Math.random();
                newScript.className = "dynamic-script"; // Idenitification ke liye
                document.body.appendChild(newScript);
            }
        });
}
/**
 * 6. Browser Navigation Support
 */
window.addEventListener('popstate', function (event) {
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('p');
    if (page) loadContent(page);
    else loadContent('components/dashboard/dashboard.php');
});

/**
 * 7. RELOAD PERSISTENCE LOGIC
 */
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const urlPage = urlParams.get('p');

    if (urlPage) {
        localStorage.setItem('last_active_page', urlPage);
        if (urlPage.toLowerCase().includes('dashboard')) {
            let retry = 0;
            const check = setInterval(() => {
                if (document.getElementById('fuelTrendChart')) {
                    initializeDashboardChart();
                    clearInterval(check);
                }
                if (++retry > 20) clearInterval(check);
            }, 100);
        }
    }
});

/**
 * 8. Dashboard Chart Logic
 */
function initializeDashboardChart() {
    const chartCanvas = document.getElementById('fuelTrendChart');
    if (!chartCanvas) return;

    if (window.myFuelChart) window.myFuelChart.destroy();

    const ctx = chartCanvas.getContext('2d');
    window.myFuelChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['10 Apr', '12 Apr', '15 Apr', '18 Apr'],
            datasets: [{
                label: 'Fuel Expense',
                data: [45000, 12000, 85000, 30000],
                backgroundColor: '#3b82f6',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });
}