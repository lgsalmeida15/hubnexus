/**
 * HubNexus - Funções Globais Auxiliares
 */

const HubNexus = {
    /**
     * Escapa strings para evitar XSS
     */
    escapeHTML: function(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },

    /**
     * Renderiza a paginação padrão do Bootstrap 5
     */
    renderPagination: function(containerId, paginationData, onPageClick) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const p = paginationData;
        let html = '';

        // Botão Anterior
        html += `
            <li class="page-item ${p.current_page === 1 ? "disabled" : ""}">
                <a class="page-link rounded-start-pill px-3" href="#" data-page="${p.current_page - 1}">Anterior</a>
            </li>
        `;

        // Páginas
        for (let i = 1; i <= p.total_pages; i++) {
            // Lógica simples para não mostrar 100 páginas (opcional, mas bom para UX)
            if (p.total_pages > 10) {
                if (i > 3 && i < p.total_pages - 2 && Math.abs(i - p.current_page) > 2) {
                    if (i === 4 || i === p.total_pages - 3) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    continue;
                }
            }

            html += `
                <li class="page-item ${i === p.current_page ? "active" : ""}">
                    <a class="page-link px-3" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        // Botão Próximo
        html += `
            <li class="page-item ${p.current_page === p.total_pages ? "disabled" : ""}">
                <a class="page-link rounded-end-pill px-3" href="#" data-page="${p.current_page + 1}">Próximo</a>
            </li>
        `;

        container.innerHTML = html;

        // Adiciona eventos de clique
        container.querySelectorAll('a.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(link.getAttribute('data-page'));
                if (page && page !== p.current_page && page > 0 && page <= p.total_pages) {
                    onPageClick(page);
                }
            });
        });
    }
};
