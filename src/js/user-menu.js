// Menu de usuário dropdown
document.addEventListener('DOMContentLoaded', function() {
    const userIcon = document.getElementById('user_icon');
    const dropdownMenu = document.getElementById('dropdown_menu');
    
    if (userIcon && dropdownMenu) {
        // Toggle dropdown ao clicar no ícone
        userIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('dropdown-hidden');
            dropdownMenu.classList.toggle('dropdown-visible');
        });
        
        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function(e) {
            if (!userIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('dropdown-visible');
                dropdownMenu.classList.add('dropdown-hidden');
            }
        });
        
        // Prevenir fechamento ao clicar dentro do dropdown
        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});