@extends('template.app')
@section('title', 'Kategori Item')
@section('subtitle', 'Kelompokkan item agar mudah dicari dan dilaporkan.')

@section('content')
  <x-master-crud base="kategori" field="category_name" :rows="$rows" :editing="$editing"
    usage="items_count" usage-label="Jumlah Item" label="Kategori" />
@endsection
