const App = {
    async ajax(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            }
        };

        if (options.body && !(options.body instanceof FormData)) {
            defaults.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(options.body);
        }

        const config = { ...defaults, ...options, headers: { ...defaults.headers, ...options.headers } };
        const fullUrl = url.startsWith('http') ? url : BASE_URL + url;

        try {
            const response = await fetch(fullUrl, config);
            const data = await response.json();

            if (!response.ok) {
                throw { status: response.status, ...data };
            }
            return data;
        } catch (error) {
            if (error.status === 401) {
                window.location.href = BASE_URL + '/auth/login';
            }
            throw error;
        }
    },

    async get(url) {
        return this.ajax(url);
    },

    async post(url, body) {
        return this.ajax(url, { method: 'POST', body });
    },

    async put(url, body) {
        return this.ajax(url, { method: 'PUT', body });
    },

    async delete(url) {
        return this.ajax(url, { method: 'DELETE' });
    },

    toast(message, type = 'success', duration = 4000) {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        toast.innerHTML = `<i class="fas fa-${icon}"></i><span>${message}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    confirm(message) {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay show';
            overlay.innerHTML = `
                <div class="modal" style="max-width:420px">
                    <div class="modal-header">
                        <h3><i class="fas fa-exclamation-triangle text-warning"></i> Confirm</h3>
                    </div>
                    <div class="modal-body"><p>${message}</p></div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" id="confirmNo">Cancel</button>
                        <button class="btn btn-danger" id="confirmYes">Confirm</button>
                    </div>
                </div>`;
            document.body.appendChild(overlay);

            overlay.querySelector('#confirmYes').onclick = () => { overlay.remove(); resolve(true); };
            overlay.querySelector('#confirmNo').onclick = () => { overlay.remove(); resolve(false); };
        });
    },

    modal: {
        open(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('show');
        },
        close(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('show');
        }
    },

    debounce(fn, delay = 300) {
        let timer;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    },

    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    },

    formatDate(dateStr) {
        if (!dateStr) return '';
        return new Date(dateStr).toLocaleString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('open');
        });
    }

    const flashAlert = document.getElementById('flashAlert');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.opacity = '0';
            flashAlert.style.transform = 'translateY(-10px)';
            flashAlert.style.transition = 'all 0.3s ease';
            setTimeout(() => flashAlert.remove(), 300);
        }, 5000);
    }

    document.querySelectorAll('.modal-close, .modal-overlay').forEach(el => {
        el.addEventListener('click', (e) => {
            if (e.target === el) {
                el.closest('.modal-overlay')?.classList.remove('show');
            }
        });
    });
});
