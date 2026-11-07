@extends('template.app')
@section('title', 'Satuan Item')
@section('subtitle', 'Satuan jual: pcs, kg, porsi, jam, dan sebagainya.')

@section('content')
  <x-master-crud base="satuan" field="unit_name" :rows="$rows" :editing="$editing"
    usage="items_count" usage-label="Jumlah Item" label="Satuan" />
@endsection
