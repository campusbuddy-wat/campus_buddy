{{-- Campus Buddy Menu Component --}}
@php
$currentRoute = Route::currentRouteName() ?? '';
@endphp

{{-- Include topbar --}}
@include('includes.topbar')