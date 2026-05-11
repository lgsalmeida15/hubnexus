<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Config;
use App\Auth;

Auth::requireLogin();

$db = Config::getDatabaseConnection();

// Busca empresas (PVs) para o filtro
$pvs = $db->query("SELECT rede_empresa_id, nome FROM empresas ORDER BY nome ASC")->fetchAll();

// Busca empresas Omie para o filtro
$empresas_omie = $db->query("SELECT id, nome FROM empresas_omie ORDER BY nome ASC")->fetchAll();

$pageTitle = 'Transações Rede - HubNexus';
$currentPage = 'hub';

include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Hub</a></li>
            <li class="breadcrumb-item"><a href="redecard.php">Redecard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Transações</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold h2">Transações Redecard</h1>
        <div id="pagination-info" class="text-muted small"></div>
    </div>

    <!-- Filtros Avançados -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form id="filter-form">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Empresa Omie</label>
                        <select name="omie_empresa_id" class="form-select">
                            <option value="">Todas as Empresas Omie</option>
                            <?php foreach ($empresas_omie as $eo): ?>
                                <option value="<?php echo $eo['id']; ?>"><?php echo htmlspecialchars($eo['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Buscar NSU</label>
                        <input type="text" name="search" class="form-control" placeholder="NSU ou NSU Parcela...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Data Rede</label>
                        <input type="date" name="data_rede" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Processado</label>
                        <select name="processado_omie" class="form-select">
                            <option value="">Todos</option>
                            <option value="true">Sim</option>
                            <option value="false">Não</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 me-2 rounded-pill"><i class="bi bi-search"></i></button>
                        <button type="reset" id="btn-reset" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-counterclockwise"></i></button>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Ponto de Venda (Múltiplo)</label>
                        <select name="empresa_id[]" class="form-select" multiple style="height: 100px;">
                            <?php foreach ($pvs as $pv): ?>
                                <option value="<?php echo $pv['rede_empresa_id']; ?>">
                                    <?php echo htmlspecialchars($pv['nome']); ?> (<?php echo $pv['rede_empresa_id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i> Mantenha Ctrl pressionado para selecionar vários</small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ações em Massa -->
    <div id="bulk-actions" class="card border-0 shadow-sm mb-4 bg-primary-subtle" style="display: none;">
        <div class="card-body p-3">
            <div class="row align-items-center g-3">
                <div class="col-md-auto">
                    <span class="fw-bold text-primary"><i class="bi bi-check2-all me-2"></i> <span id="selected-count">0</span> selecionados</span>
                </div>
                <div class="col-md">
                    <input type="text" id="bulk-observacao" class="form-control form-control-sm" placeholder="Nova observação...">
                </div>
                <div class="col-md-auto">
                    <select id="bulk-processado" class="form-select form-select-sm">
                        <option value="">Processado?</option>
                        <option value="true">Sim</option>
                        <option value="false">Não</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button onclick="applyBulkUpdate()" class="btn btn-primary btn-sm rounded-pill px-4">Aplicar em Massa</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 position-relative">
        <div class="loading-overlay" id="loading" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(var(--bs-body-bg-rgb), 0.7); display: none; justify-content: center; align-items: center; z-index: 10;">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="ps-4">
                                <input class="form-check-input" type="checkbox" id="select-all">
                            </th>
                            <th>Data Rede</th>
                            <th>Empresa Omie</th>
                            <th>Ponto de Venda</th>
                            <th>NSU</th>
                            <th>Parcela</th>
                            <th>Status</th>
                            <th>Observação</th>
                            <th class="text-center">Processado</th>
                            <th class="text-center pe-4">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <!-- Preenchido via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent border-top d-flex justify-content-center py-3">
            <nav>
                <ul class="pagination mb-0" id="pagination">
                    <!-- Preenchido via AJAX -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php 
$extraScripts = <<<'JS'
<script>
    let currentPage = 1;

    async function fetchTransactions(page = 1) {
        currentPage = page;
        const loading = document.getElementById("loading");
        const tableBody = document.getElementById("table-body");
        const pagination = document.getElementById("pagination");
        const paginationInfo = document.getElementById("pagination-info");
        
        loading.style.display = "flex";
        
        const form = document.getElementById("filter-form");
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (const [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        params.append("page", page);
        
        try {
            const response = await fetch(`api/rede_transacoes.php?${params.toString()}`);
            const result = await response.json();
            
            tableBody.innerHTML = "";
            
            if (result.data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="10" class="text-center py-5 text-muted"><i class="bi bi-search fs-2 d-block mb-3"></i> Nenhuma transação encontrada.</td></tr>';
                pagination.innerHTML = "";
                paginationInfo.innerText = "";
            } else {
                result.data.forEach(t => {
                    let badgeClass = "bg-secondary-subtle text-secondary border border-secondary-subtle";
                    if (t.status === "PROCESSADO") badgeClass = "bg-success-subtle text-success border border-success-subtle";
                    else if (t.status.includes("ERRO")) badgeClass = "bg-danger-subtle text-danger border border-danger-subtle";
                    else if (t.status === "PENDENTE") badgeClass = "bg-info-subtle text-info border border-info-subtle";
                    
                    const isProc = (t.processado_omie === true || t.processado_omie == "t" || t.processado_omie == 1);

                    tableBody.innerHTML += `
                        <tr data-id="${t.id}">
                            <td class="ps-4">
                                <input class="form-check-input row-checkbox" type="checkbox" value="${t.id}">
                            </td>
                            <td class="small fw-medium">${t.data_rede_fmt}</td>
                            <td><small class="text-muted">${HubNexus.escapeHTML(t.omie_nome) || "-"}</small></td>
                            <td><small class="fw-bold">${HubNexus.escapeHTML(t.empresa_nome) || "ID: " + t.rede_empresa_id}</small></td>
                            <td><code class="text-primary">${HubNexus.escapeHTML(t.nsu)}</code></td>
                            <td class="small">${t.parcela_numero}/${t.parcela_total}</td>
                            <td><span class="badge ${badgeClass} rounded-pill px-2">${HubNexus.escapeHTML(t.status)}</span></td>
                            <td>
                                <input type="text" class="form-control form-control-sm small" 
                                       value="${HubNexus.escapeHTML(t.observacao || "")}" 
                                       onchange="updateSingleField(${t.id}, 'observacao', this.value)">
                            </td>
                            <td class="text-center">
                                <select class="form-select form-select-sm small" 
                                        onchange="updateSingleField(${t.id}, 'processado_omie', this.value)">
                                    <option value="false" ${!isProc ? "selected" : ""}>Não</option>
                                    <option value="true" ${isProc ? "selected" : ""}>Sim</option>
                                </select>
                            </td>
                            <td class="text-center pe-4">
                                <a href="rede_transacoes_view.php?id=${t.id}" class="btn btn-sm btn-outline-primary rounded-circle">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
                
                attachCheckboxEvents();
                HubNexus.renderPagination("pagination", result.pagination, fetchTransactions);
                paginationInfo.innerHTML = `Mostrando <span class="fw-bold text-primary">${result.data.length}</span> de <span class="fw-bold">${result.pagination.total_records}</span> registros`;
            }
        } catch (error) {
            console.error("Erro ao buscar transações:", error);
            tableBody.innerHTML = '<tr><td colspan="10" class="text-center py-5 text-danger">Erro ao carregar dados.</td></tr>';
        } finally {
            loading.style.display = "none";
        }
    }

    function attachCheckboxEvents() {
        const selectAll = document.getElementById("select-all");
        const rowCheckboxes = document.querySelectorAll(".row-checkbox");
        const bulkSection = document.getElementById("bulk-actions");
        const selectedCount = document.getElementById("selected-count");

        selectAll.checked = false;
        bulkSection.style.display = "none";

        selectAll.addEventListener("change", () => {
            rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkVisibility();
        });

        rowCheckboxes.forEach(cb => {
            cb.addEventListener("change", updateBulkVisibility);
        });

        function updateBulkVisibility() {
            const checked = document.querySelectorAll(".row-checkbox:checked");
            bulkSection.style.display = checked.length > 0 ? "block" : "none";
            selectedCount.innerText = checked.length;
        }
    }

    async function updateSingleField(id, field, value) {
        const formData = new FormData();
        formData.append("ids[]", id);
        formData.append(field, value);
        formData.append("csrf_token", HubNexus.csrfToken);
        
        try {
            const response = await fetch("api/bulk_update_transacoes.php", {
                method: "POST",
                body: formData
            });
            const result = await response.json();
            if (!result.success) alert("Erro ao atualizar campo: " + result.error);
        } catch (error) {
            alert("Erro de conexão ao atualizar campo.");
        }
    }

    async function applyBulkUpdate() {
        const checked = document.querySelectorAll(".row-checkbox:checked");
        const ids = Array.from(checked).map(cb => cb.value);
        const obs = document.getElementById("bulk-observacao").value;
        const proc = document.getElementById("bulk-processado").value;

        if (!obs && !proc) {
            alert("Preencha ao menos um campo para replicar.");
            return;
        }

        const formData = new FormData();
        ids.forEach(id => formData.append("ids[]", id));
        if (obs) formData.append("observacao", obs);
        if (proc) formData.append("processado_omie", proc);
        formData.append("csrf_token", HubNexus.csrfToken);

        try {
            const response = await fetch("api/bulk_update_transacoes.php", {
                method: "POST",
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                fetchTransactions(currentPage);
            } else {
                alert("Erro: " + result.error);
            }
        } catch (error) {
            alert("Erro de conexão ao processar lote.");
        }
    }

    document.getElementById("filter-form").addEventListener("submit", (e) => {
        e.preventDefault();
        fetchTransactions(1);
    });

    document.getElementById("btn-reset").addEventListener("click", () => {
        document.getElementById("filter-form").reset();
        setTimeout(() => fetchTransactions(1), 10);
    });

    fetchTransactions(1);
</script>
JS;
include 'includes/footer.php'; 
?>
