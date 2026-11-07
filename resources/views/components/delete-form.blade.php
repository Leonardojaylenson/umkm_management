@props(['action', 'message' => 'Yakin ingin menghapus data ini?', 'title' => 'Hapus'])

<form action="{{ $action }}" method="POST" class="d-inline" onsubmit="return confirm({{ \Illuminate\Support\Js::from($message) }})">
  @csrf
  @method('DELETE')
  <button type="submit" class="table-btn-action delete" title="{{ $title }}"><i class="bi bi-trash"></i></button>
</form>
