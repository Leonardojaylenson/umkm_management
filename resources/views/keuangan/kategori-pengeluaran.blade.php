@extends('template.app')
@section('title', 'Kategori Pengeluaran')
@section('subtitle', 'Contoh: Bahan, Gaji, Sewa, Listrik.')

@section('content')
  <x-master-crud base="kategori-pengeluaran" field="category_name" :rows="$rows" :editing="$editing"
    usage="expenses_count" usage-label="Jumlah Catatan" label="Kategori pengeluaran" />
@endsection
