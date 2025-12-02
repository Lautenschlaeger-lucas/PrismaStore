<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="src/images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="src/styles/simulador.css">
    <link rel="stylesheet" href="src/styles/user-menu.css">
    <title>PrismaStore - Simulador</title>
</head>
<body>
    <header>
        <nav id="navbar">
            <a href="PainelStore.php">
                <img id="logo" src="src/images/logo.png" alt="PrismaStore">
            </a>
            <ul id="nav_list">
                <li class="nav_item">
                    <a href="PainelStore.php">Início</a>
                </li>
                <li class="nav_item">
                    <a href="src/php/Paineis.php">Orçamentos</a>
                </li>
                <li class="nav_item">
                    <a href="simulador.php" class="active">Simulador</a>
                </li>
            </ul>

            <!-- Menu de usuário -->
            <div id="user_menu_container">
                <?php if ($is_logged_in): ?>
                    <div id="user_icon" class="logged-in">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div id="dropdown_menu" class="dropdown-hidden">
                        <a href="orcamento.php" class="dropdown-item">
                            <i class="fa-solid fa-shopping-cart"></i>
                            <span>Carrinho</span>
                        </a>
                        <a href="src/php/dashboard.php" class="dropdown-item">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="#" onclick="fazerLogout(); return false;" class="dropdown-item logout">
                            <i class="fa-solid fa-sign-out-alt"></i>
                            <span>Sair</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div id="login_icon">
                        <a href="src/php/login.php">
                            <i class="fa-regular fa-user"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <section id="simulador">
            <div class="shape"></div>
            <div class="simulator-container">
                <h1 class="simulator-title">Simulador Solar</h1>
                <p class="simulator-subtitle">Descubra quantos painéis solares você precisa e quanto pode economizar</p>
                
                <div class="simulator-grid">
                    <!-- Formulário de Entrada -->
                    <div class="form-container">
                        <h2 class="form-title">
                            <i class="fas fa-calculator"></i>
                            Dados para Simulação
                        </h2>
                        
                        <div class="input-group">
                            <label class="input-label" for="consumo-mensal">
                                Consumo Mensal de Energia (kWh)
                            </label>
                            <input 
                                type="number" 
                                id="consumo-mensal" 
                                class="input-field" 
                                placeholder="Ex: 300"
                                min="1"
                            >
                        </div>

                        <div class="input-group">
                            <label class="input-label" for="potencia-placa">
                                Potência da Placa Solar (W)
                            </label>
                            <input 
                                type="number" 
                                id="potencia-placa" 
                                class="input-field" 
                                placeholder="Ex: 550"
                                min="1"
                            >
                        </div>

                        <div class="input-group">
                            <label class="input-label" for="preco-placa">
                                Preço por Placa Solar (R$)
                            </label>
                            <input 
                                type="number" 
                                id="preco-placa" 
                                class="input-field" 
                                placeholder="Ex: 800"
                                min="1"
                                step="0.01"
                            >
                        </div>

                        <div class="input-group">
                            <label class="input-label" for="area-telhado">
                                Área Disponível no Telhado (m²)
                            </label>
                            <input 
                                type="number" 
                                id="area-telhado" 
                                class="input-field" 
                                placeholder="Ex: 50"
                                min="1"
                                step="0.1"
                            >
                        </div>

                        <div class="input-group">
                            <label class="input-label" for="tarifa-energia">
                                Tarifa de Energia (R$/kWh)
                            </label>
                            <input 
                                type="number" 
                                id="tarifa-energia" 
                                class="input-field" 
                                placeholder="Ex: 0.75"
                                min="0.01"
                                step="0.01"
                                value="0.75"
                            >
                        </div>

                        <button class="calculate-btn" onclick="calcularSimulacao()">
                            <i class="fas fa-solar-panel"></i>
                            Calcular Simulação
                        </button>
                    </div>

                    <!-- Resultados -->
                    <div class="results-container">
                        <div class="results-header">
                            <h2 class="results-title">
                                <i class="fas fa-chart-line"></i>
                                Resultados da Simulação
                            </h2>
                        </div>
                        <div class="results-content" id="results-content">
                            <div class="results-placeholder">
                                <i class="fas fa-sun"></i>
                                <p>Preencha os dados acima para ver os resultados da simulação</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div id="footer_items">
            <span id="copyright">
                &copy; 2025 PrismaStore
            </span>
            <div class="social-media-buttons">
                <a href="https://www.whatsapp.com/?lang=pt_BR">
                    <i class="fa-brands fa-whatsapp"></i>
                </a>
                <a href="https://www.instagram.com/">
                    <i class="fa-brands fa-instagram"></i>
                </a>
                <a href="https://www.facebook.com/login/?locale=pt_BR">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
            </div>
        </div>
    </footer>

    <script src="src/js/user-menu.js"></script>
    <script>
        function calcularSimulacao() {
            const consumoMensal = parseFloat(document.getElementById('consumo-mensal').value);
            const potenciaPlaca = parseFloat(document.getElementById('potencia-placa').value);
            const precoPlaca = parseFloat(document.getElementById('preco-placa').value);
            const areaTelhado = parseFloat(document.getElementById('area-telhado').value);
            const tarifaEnergia = parseFloat(document.getElementById('tarifa-energia').value);

            if (!consumoMensal || !potenciaPlaca || !precoPlaca || !areaTelhado || !tarifaEnergia) {
                alert('Por favor, preencha todos os campos!');
                return;
            }

            const horasSolPorDia = 5.5;
            const diasPorMes = 30;
            const areaPlaca = 2.5;
            const eficiencia = 0.85;
            const fatorEmissaoCO2 = 0.0817;

            const energiaMensalPorPlaca = (potenciaPlaca / 1000) * horasSolPorDia * diasPorMes * eficiencia;
            const placasNecessarias = Math.ceil(consumoMensal / energiaMensalPorPlaca);
            const areaOcupada = placasNecessarias * areaPlaca;
            const investimentoTotal = placasNecessarias * precoPlaca;
            const economiaAnual = consumoMensal * 12 * tarifaEnergia;
            const paybackAnos = investimentoTotal / economiaAnual;
            const co2NaoEmitido10Anos = consumoMensal * 12 * 10 * fatorEmissaoCO2 / 1000;

            const areaSuficiente = areaOcupada <= areaTelhado;

            exibirResultados({
                placasNecessarias,
                areaOcupada,
                investimentoTotal,
                paybackAnos,
                co2NaoEmitido10Anos,
                areaSuficiente,
                areaTelhado,
                economiaAnual,
                energiaMensalTotal: placasNecessarias * energiaMensalPorPlaca
            });
        }

        function exibirResultados(resultados) {
            const resultsContent = document.getElementById('results-content');
            
            let alertClass = 'alert-success';
            let alertMessage = 'Sua área de telhado é suficiente para a instalação!';
            let alertIcon = 'fas fa-check-circle';

            if (!resultados.areaSuficiente) {
                alertClass = 'alert-error';
                alertMessage = 'Área do telhado insuficiente! Considere reduzir o consumo ou usar placas de maior potência.';
                alertIcon = 'fas fa-exclamation-triangle';
            } else if (resultados.areaOcupada / resultados.areaTelhado > 0.8) {
                alertClass = 'alert-warning';
                alertMessage = 'A instalação ocupará mais de 80% da área disponível.';
                alertIcon = 'fas fa-exclamation-circle';
            }

            resultsContent.innerHTML = `
                <div class="result-card">
                    <div class="result-label"><i class="fas fa-solar-panel"></i> Quantidade de Placas</div>
                    <div class="result-value">${resultados.placasNecessarias} placas</div>
                    <div class="result-description">Gerando aproximadamente ${Math.round(resultados.energiaMensalTotal)} kWh/mês</div>
                </div>
                <div class="result-card">
                    <div class="result-label"><i class="fas fa-ruler-combined"></i> Área Ocupada</div>
                    <div class="result-value">${resultados.areaOcupada.toFixed(1)} m²</div>
                    <div class="result-description">${((resultados.areaOcupada / resultados.areaTelhado) * 100).toFixed(1)}% da área disponível</div>
                </div>
                <div class="result-card">
                    <div class="result-label"><i class="fas fa-money-bill-wave"></i> Investimento Total</div>
                    <div class="result-value">R$ ${resultados.investimentoTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
                    <div class="result-description">Economia anual: R$ ${resultados.economiaAnual.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
                </div>
                <div class="result-card">
                    <div class="result-label"><i class="fas fa-calendar-alt"></i> Tempo de Payback</div>
                    <div class="result-value">${resultados.paybackAnos.toFixed(1)} anos</div>
                    <div class="result-description">Tempo para recuperar o investimento</div>
                </div>
                <div class="result-card">
                    <div class="result-label"><i class="fas fa-leaf"></i> CO² Não Emitido (10 anos)</div>
                    <div class="result-value">${resultados.co2NaoEmitido10Anos.toFixed(2)} toneladas</div>
                    <div class="result-description">Contribuição para o meio ambiente</div>
                </div>
                <div class="alert ${alertClass}">
                    <i class="${alertIcon}"></i>
                    ${alertMessage}
                </div>
            `;
        }

        function fazerLogout() {
            if (confirm('Deseja realmente sair?')) {
                fetch('src/php/logout.php', {
                    method: 'POST',
                    credentials: 'same-origin'
                })
                .then(response => {
                    window.location.href = 'simulador.php';
                })
                .catch(error => {
                    console.error('Erro:', error);
                    window.location.href = 'simulador.php';
                });
            }
        }
    </script>
</body>
</html>