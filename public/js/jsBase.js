function dragModal(modal) {
    const encabezado = modal.querySelector('.cardPrin-header');
    if (!encabezado) return;
    let offset = { x: 0, y: 0 };
    const mover = (e) => {
        modal.style.left = `${e.clientX - offset.x}px`;
        modal.style.top = `${e.clientY - offset.y}px`;
    };
    encabezado.addEventListener('mousedown', (e) => {
        const rect = modal.getBoundingClientRect();
        offset = { x: e.clientX - rect.left, y: e.clientY - rect.top };
        Object.assign(modal.style, { position: 'fixed', left: `${rect.left}px`, top: `${rect.top}px`, margin: '0' });
        document.addEventListener('mousemove', mover);
        document.body.style.userSelect = 'none';
    });
    document.addEventListener('mouseup', () => {
        document.removeEventListener('mousemove', mover);
        document.body.style.userSelect = '';
    });
}
//Sweet alert
document.addEventListener('livewire:init', () => {
    Livewire.on('sweetalert', (data) => {
        if (Array.isArray(data) && data.length === 1 && typeof data[0] === 'object') {
            data = data[0];
        }
        Swal.fire({
            icon: data.icon ?? 'success',
            title: data.text ?? '',
            showConfirmButton: false,
            timer: data.timer ?? 10000,
            timerProgressBar: true,
            target: document.body,
            customClass: {
                container: 'modalSweet'
            }
        });
    });
});
// VER PASSWORD
function togglePassword() {
    const passwordInput = document.getElementById('password');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
    } else {
        passwordInput.type = 'password';
    }
}
// Autoselect  
document.addEventListener('focusin', function(e) {
    if (
        e.target.classList.contains('inpBase') ||
        e.target.classList.contains('inpChico')||
        e.target.classList.contains('inpSolo')
    ) {
        e.target.select();
    }
});
//tabs
document.querySelectorAll('.ficha').forEach(contenedor => {
    const botones = contenedor.querySelectorAll('.ficha-boton');
    const contenidos = contenedor.querySelectorAll('.ficha-body');

    botones.forEach((boton, indice) => {
        boton.addEventListener('click', () => {
            botones.forEach(item => item.classList.remove('active'));
            contenidos.forEach(item => item.classList.remove('active'));

            boton.classList.add('active');
            contenidos[indice].classList.add('active');
        });
    });
});