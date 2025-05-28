<form id="formAplicacion" method="post" action="../controllers/AplicacionesController.php?action=save">
    <?php if (isset($aplicacion)): ?>
        <input type="hidden" name="SeAplCodigo" value="<?= $aplicacion['SeAplCodigo'] ?>">
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" name="SeAplDescripcion" class="form-control" required
                       value="<?= isset($aplicacion) ? htmlspecialchars($aplicacion['SeAplDescripcion']) : '' ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Icono (Font Awesome)</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-icons"></i></span>
                    </div>
                    <input type="text" name="SeAplFontIcon" class="form-control" 
                           value="<?= isset($aplicacion) ? $aplicacion['SeAplFontIcon'] : '' ?>">
                </div>
                <small class="text-muted">Ejemplo: fa-folder, fa-user, etc.</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Tipo</label>
                <select name="SeAplTipo" class="form-control select2" style="width: 100%;" required>
                    <option value="MEN" <?= (isset($aplicacion) && $aplicacion['SeAplTipo'] == 'MEN') ? 'selected' : '' ?>>Menú</option>
                    <option value="SUB" <?= (isset($aplicacion) && $aplicacion['SeAplTipo'] == 'SUB') ? 'selected' : '' ?>>Submenú</option>
                    <option value="APL" <?= (isset($aplicacion) && $aplicacion['SeAplTipo'] == 'APL') ? 'selected' : '' ?>>Aplicación</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Estado</label>
                <select name="SeAplEstado" class="form-control select2" style="width: 100%;" required>
                    <option value="A" <?= (isset($aplicacion) && $aplicacion['SeAplEstado'] == 'A') ? 'selected' : '' ?>>Activo</option>
                    <option value="I" <?= (isset($aplicacion) && $aplicacion['SeAplEstado'] == 'I') ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Sistema</label>
                <select name="sistcod" class="form-control select2" style="width: 100%;" required>
                    <option value="">Seleccione un sistema</option>
                    <?php foreach ($sistemas as $sistema): ?>
                        <option value="<?= $sistema['sistcod'] ?>" 
                            <?= (isset($aplicacion) && $aplicacion['sistcod'] == $sistema['sistcod']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sistema['sistnom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Menú Padre</label>
                <select name="SeAplCodigoSt" class="form-control select2" style="width: 100%;">
                    <option value="">Ninguno (Menú principal)</option>
                    <?php foreach ($menusPadre as $menu): ?>
                        <option value="<?= $menu['SeAplCodigo'] ?>" 
                            <?= (isset($aplicacion) && $aplicacion['SeAplCodigoSt'] == $menu['SeAplCodigo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($menu['SeAplDescripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Nombre del Objeto (Ruta)</label>
                <input type="text" name="SeAplNombreObjeto" class="form-control"
                       value="<?= isset($aplicacion) ? htmlspecialchars($aplicacion['SeAplNombreObjeto']) : '' ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Orden</label>
                <input type="number" name="SeAplOrden" class="form-control" required
                       value="<?= isset($aplicacion) ? $aplicacion['SeAplOrden'] : '0' ?>">
            </div>
        </div>
    </div>

    <div class="form-group">
        <input type="hidden" name="SeAplUserCreacion" class="form-control" value="<?= htmlspecialchars($_POST['SeAplUserCreacion'] ?? '01005') ?>" maxlength="10" pattern="[0-9]{1,10}" title="Solo números, máximo 10 dígitos">
        <input type="hidden" name="SeAplUserModificacion" value="<?= $_SESSION['user']['usuario_codigo'] ?>">
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Guardar
        </button>
        <button type="button" class="btn btn-default" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Inicializar select2 en el modal
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Opcional: Actualizar menú padre según el tipo seleccionado
    $('select[name="SeAplTipo"]').on('change', function() {
        if($(this).val() === 'MEN') {
            $('select[name="SeAplCodigoSt"]').val('').trigger('change');
        }
    });
});
</script>