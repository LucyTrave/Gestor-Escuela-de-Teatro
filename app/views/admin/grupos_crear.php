<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: rgba(0, 0, 0, 0.45);
}

.modal-container {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: #ffffff;
    width: 430px;
    padding: 24px;
    border-radius: 6px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
}

.modal-content h2 {
    margin: 0 0 6px;
    font-size: 18px;
}

.modal-content p {
    margin-bottom: 20px;
    color: #666;
    font-size: 14px;
}

label {
    display: block;
    font-weight: bold;
    font-size: 13px;
    margin-bottom: 6px;
}

input,
select {
    width: 100%;
    padding: 10px;
    margin-bottom: 14px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}

.fila {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.acciones {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
}

.btn-cancelar {
    padding: 10px 16px;
    border: 1px solid #ddd;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
    background: #fff;
}

.btn-crear {
    padding: 10px 16px;
    border: none;
    background: #b0002b;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}
</style>


<div class="modal-container">
    <div class="modal-content">

        <h2>Crear Nuevo Grupo de Clases</h2>
        <p>Completa los datos para crear un nuevo grupo de clases</p>

        <!-- FORMULARIO -->
        <form action="<?= BASE_URL ?>/admin/grupos/crear" method="POST">
            <?= Csrf::field() ?>

            
            <!-- Nombre -->
        <label>Nombre del grupo *</label>

        <select name="nombre" id="selectNombreGrupo" required>
            <option value="">Selecciona un grupo</option>
            <option value="Iniciación">Iniciación</option>
            <option value="Iniciación 2º Año">Iniciación 2º Año</option>
            <option value="Avanzado">Avanzado</option>
            <option value="otro">Otro nombre</option>
        </select>

         <div id="campoOtroNombreGrupo" style="
            display:none;
            margin-top:24px;
             margin-bottom:34px;
            padding-bottom:22px;
            border-bottom:1px dashed #cbd5e1;
            ">

        <div style="
            display:flex;
            align-items:center;
            gap:10px;
            color:#2563eb;
            font-weight:700;
            font-size:16px;
            border-bottom:2px solid #2563eb;
            padding-bottom:8px;
            margin-bottom:10px;
            ">
        <span style="font-size:18px;">✎</span>
        <span>Escribe un nombre</span>
        </div>

        <p style="
            margin:0 0 14px 0;
            color:#64748b;
            font-size:13px;
            ">
            Puedes escribir el nombre que desees para este grupo.
        </p>

    <input 
        type="text" 
        name="otro_nombre_grupo" 
        id="otro_nombre_grupo"
        placeholder="Escribe el nombre del grupo aquí..."
        style="
            width:100%;
            border:1px solid #2563eb;
            border-radius:8px;
            padding:11px 12px;
            font-size:14px;
        ">
</div>

            <!-- Día y Hora -->
            <div class="fila">
                <div>
                    <label>Día de la semana *</label>
                    <select name="dia_semana" required>
                        <option value="">Selecciona un día</option>
                        <option value="lunes">Lunes</option>
                        <option value="martes">Martes</option>
                        <option value="miercoles">Miércoles</option>
                        <option value="jueves">Jueves</option>
                        <option value="viernes">Viernes</option>
                        <option value="sabado">Sábado</option>
                    </select>
                </div>

                <div>
                    <label>Hora *</label>
                    <input type="time" name="hora_inicio" required>
                </div>
            </div>

            <!-- Profesor -->
            <label>Profesor *</label>
            <select name="profesor_id" required>
                <option value="">Selecciona un profesor</option>
                <?php foreach ($profesores as $p): ?>
                    <option value="<?= htmlspecialchars($p['usuario_id']) ?>">
                        <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Nivel -->
            <label>Nivel *</label>
            <select name="nivel" required>
                <option value="iniciacion">Iniciación</option>
                <option value="intermedio">Intermedio</option>
                <option value="avanzado">Avanzado</option>
            </select>

            <!-- Sala -->
            <label>Sala y Espacio *</label>
            <select name="sala_id" required>
                <option value="">Selecciona una sala</option>
                <!-- Aquí luego cargarás desde BD -->
                <option value="1">Sala Teatro</option>
                <option value="2">Sala Blanca</option>
                <option value="3">Sala Madera</option>
            </select>

            <!-- Aforo -->
            <label>Aforo máximo *</label>
            <input type="number" name="aforo_maximo" value="16" min="1" required>

            <!-- BOTONES -->
            <div class="acciones">
                <a href="<?= BASE_URL ?>/admin/grupos" class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn-crear">Crear Grupo</button>
            </div>

        </form>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectNombreGrupo = document.getElementById('selectNombreGrupo');
    const campoOtroNombreGrupo = document.getElementById('campoOtroNombreGrupo');
    const inputOtroNombreGrupo = document.getElementById('otro_nombre_grupo');

    selectNombreGrupo.addEventListener('change', function () {
        if (this.value === 'otro') {
            campoOtroNombreGrupo.style.display = 'block';
            inputOtroNombreGrupo.required = true;
        } else {
            campoOtroNombreGrupo.style.display = 'none';
            inputOtroNombreGrupo.required = false;
            inputOtroNombreGrupo.value = '';
        }
    });
});
</script>



</div>