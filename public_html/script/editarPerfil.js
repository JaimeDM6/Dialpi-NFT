

document.addEventListener('DOMContentLoaded', function() {
    const editarPerfilBtn = document.getElementById('editar-perfil');
    const form = document.getElementById('perfil-form');
    const inputs = form.querySelectorAll('.perfil-input');
    
    editarPerfilBtn.addEventListener('click', function() {
        // Habilitar campos del formulario para edición
        inputs.forEach(input => input.removeAttribute('readonly'));
        
        // Cambiar el botón a "Guardar"
        editarPerfilBtn.textContent = 'Guardar';
        editarPerfilBtn.classList.remove('button__perfil__editar');
        editarPerfilBtn.classList.add('button__perfil__guardar');
        
        // Cambiar la acción del botón para que guarde los datos
        editarPerfilBtn.removeEventListener('click', arguments.callee);
        editarPerfilBtn.addEventListener('click', function() {
            // Crear un campo oculto para indicar que se están modificando los datos
            const modificarInput = document.createElement('input');
            modificarInput.type = 'hidden';
            modificarInput.name = 'modificar';
            modificarInput.value = '1';
            form.appendChild(modificarInput);
            
            // Enviar el formulario
            form.submit();
        });

        // Activar la función para revelar la tarjeta solo si el formulario está en modo edición
        if (editarPerfilBtn.classList.contains('button__perfil__editar')) {
            revelarTarjeta();
            
        }
    });
});
