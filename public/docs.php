<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Auth;

Auth::requireLogin();

$pageTitle = 'Documentação Técnica - Redecard';
$currentPage = 'hub';

include 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Hub</a></li>
            <li class="breadcrumb-item"><a href="redecard.php">Redecard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Documentação Técnica</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm mb-5">
                <div class="card-body p-5">
                    <div id="render-doc" class="markdown-body">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Carregando documentação do motor...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$extraHead = '
<!-- Prism.js para syntax highlighting -->
<link href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
<style>
    .markdown-body h1 { border-bottom: 2px solid var(--hub-primary); padding-bottom: 15px; margin-top: 40px; font-weight: 700; }
    .markdown-body h2 { margin-top: 35px; color: var(--hub-primary); border-bottom: 1px solid var(--bs-border-color); padding-bottom: 8px; font-weight: 600; }
    .markdown-body pre { background: #1e1e1e !important; border-radius: 12px; padding: 20px !important; margin: 25px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    .markdown-body code { color: var(--hub-primary); background: rgba(75, 0, 129, 0.05); padding: 2px 5px; border-radius: 4px; }
    .markdown-body pre code { color: #e0e0e0; background: transparent; padding: 0; }
    .markdown-body table { width: 100%; border-collapse: separate; border-spacing: 0; margin: 25px 0; border-radius: 10px; overflow: hidden; border: 1px solid var(--bs-border-color); }
    .markdown-body th, .markdown-body td { border: 1px solid var(--bs-border-color); padding: 15px; }
    .markdown-body th { background: var(--bs-tertiary-bg); font-weight: 700; }
</style>
';

$extraScripts = '
<!-- Scripts para Markdown e Syntax Highlight -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-php.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-sql.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-json.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-bash.min.js"></script>
<script>
    async function loadDoc() {
        try {
            const response = await fetch(`api/get_doc.php?file=projeto.md`);
            const data = await response.json();
            if (data.content) {
                document.getElementById("render-doc").innerHTML = marked.parse(data.content);
                Prism.highlightAll();
            } else {
                document.getElementById("render-doc").innerHTML = `<div class="alert alert-danger border-0">Erro ao carregar documentação: ${data.error}</div>`;
            }
        } catch (error) {
            document.getElementById("render-doc").innerHTML = `<div class="alert alert-danger border-0">Erro de conexão com a API.</div>`;
        }
    }
    document.addEventListener("DOMContentLoaded", loadDoc);
</script>
';
include 'includes/footer.php'; 
?>

