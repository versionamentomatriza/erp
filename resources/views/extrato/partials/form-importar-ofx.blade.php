<form action="{{ route('extrato.conciliar.post') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label for="arquivos_ofx" class="form-label">Importe arquivos OFX ou selecione a conciliação desejada</label>
    <div class="input-group mb-3">
        <input class="form-control" type="file" name="arquivos_ofx[]" id="arquivos_ofx" accept=".ofx" multiple required>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-upload"></i> Importar
        </button>
    </div>
</form>