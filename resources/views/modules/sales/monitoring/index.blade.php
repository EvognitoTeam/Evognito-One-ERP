@extends('layouts.workspace')
@section('title', __('sales.monitoring.title').' - Sales Force')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
#sales-monitoring-map{position:relative;z-index:0;background:#efeee9}
#sales-monitoring-map .leaflet-pane,#sales-monitoring-map .leaflet-tile,#sales-monitoring-map .leaflet-marker-icon,#sales-monitoring-map .leaflet-marker-shadow,#sales-monitoring-map .leaflet-tile-container,#sales-monitoring-map .leaflet-overlay-pane svg{position:absolute;left:0;top:0}
#sales-monitoring-map img,#sales-monitoring-map img.leaflet-tile,#sales-monitoring-map .leaflet-marker-icon,#sales-monitoring-map .leaflet-marker-shadow{max-width:none!important;max-height:none!important}
#sales-monitoring-map img.leaflet-tile{width:256px!important;height:256px!important}
.sales-pin-wrapper{background:transparent!important;border:0!important}
.sales-pin{position:relative;width:40px;height:50px;filter:drop-shadow(0 4px 5px rgba(0,0,0,.2))}
.sales-pin svg{width:40px;height:50px}.sales-pin-label{position:absolute;left:50%;top:13px;transform:translateX(-50%);font-size:11px;font-weight:800;color:#fff}
</style>
@endpush

@section('content')
@php $employeeName = fn($employee) => $employee?->name ?? $employee?->user?->name ?? '-'; @endphp
<section class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between"><div><div class="text-xs font-bold uppercase tracking-[0.12em] text-teal-700">Sales Force</div><h1 class="mt-2 text-3xl font-semibold tracking-[-0.04em]">{{ __('sales.monitoring.title') }}</h1><p class="mt-3 max-w-3xl text-sm leading-6 text-[#77746d]">{{ __('sales.monitoring.description') }}</p></div><div class="rounded-full border border-teal-100 bg-teal-50 px-4 py-2 text-xs font-semibold text-teal-700">{{ __('sales.monitoring.auto_refresh') }}</div></section>
<div class="mt-8">@include('modules.sales.partials.navigation')</div>
<section class="mt-5 rounded-2xl border border-black/[0.06] bg-white p-5"><form method="GET" class="grid gap-3 md:grid-cols-[220px_1fr_auto]"><input type="date" name="date" value="{{ $date }}" class="h-11 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"><select name="employee_id" class="h-11 rounded-xl border border-black/[0.08] bg-[#faf9f6] px-4 text-sm"><option value="">{{ __('sales.monitoring.all_sales') }}</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected((string)$employeeId===(string)$employee->id)>{{ $employeeName($employee) }}{{ $employee->employee_number ? ' — '.$employee->employee_number : '' }}</option>@endforeach</select><button class="h-11 rounded-xl bg-[#1d1d1b] px-5 text-sm font-semibold text-white">{{ __('common.filter') }}</button></form></section>
<section class="mt-5 grid overflow-hidden rounded-2xl border border-black/[0.06] bg-white xl:grid-cols-[minmax(0,1fr)_340px]"><div class="min-w-0"><div id="sales-monitoring-map" class="h-[620px] w-full"></div></div><aside class="border-t border-black/[0.06] xl:border-l xl:border-t-0"><div class="border-b border-black/[0.06] p-5"><h2 class="text-lg font-semibold">{{ __('sales.monitoring.live_sales') }}</h2><p id="monitor-summary" class="mt-1 text-xs leading-5 text-[#85827b]">{{ __('sales.monitoring.loading') }}</p></div><div id="monitor-list" class="max-h-[620px] overflow-y-auto"><div class="p-5 text-sm text-[#85827b]">{{ __('sales.monitoring.loading') }}</div></div></aside></section>
<section class="mt-5 grid gap-4 md:grid-cols-3"><article class="rounded-2xl border border-black/[0.06] bg-white p-5"><div class="text-xs font-semibold text-[#85827b]">{{ __('sales.configuration.moving_interval') }}</div><div class="mt-2 text-2xl font-semibold">{{ $policy->tracking_moving_interval_seconds }}s</div></article><article class="rounded-2xl border border-black/[0.06] bg-white p-5"><div class="text-xs font-semibold text-[#85827b]">{{ __('sales.configuration.active_interval') }}</div><div class="mt-2 text-2xl font-semibold">{{ $policy->tracking_active_interval_seconds }}s</div></article><article class="rounded-2xl border border-black/[0.06] bg-white p-5"><div class="text-xs font-semibold text-[#85827b]">{{ __('sales.configuration.idle_interval') }}</div><div class="mt-2 text-2xl font-semibold">{{ $policy->tracking_idle_interval_seconds }}s</div></article></section>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(() => {
    const mapEl=document.getElementById('sales-monitoring-map'); if(!mapEl||!window.L)return;
    const map=L.map(mapEl,{zoomControl:true}).setView([-2.5489,118.0149],5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap contributors'}).addTo(map);
    let layers=[];
    const endpoint=@js(route('sales.monitoring.data',['organization'=>$organization->slug]));
    const date=@js($date); const employeeId=@js($employeeId);
    const labels={moving:@js(__('sales.monitoring.moving')),idle:@js(__('sales.monitoring.idle')),battery:@js(__('sales.monitoring.battery')),accuracy:@js(__('sales.monitoring.accuracy')),last:@js(__('sales.monitoring.last_update')),points:@js(__('sales.monitoring.points')),sales:@js(__('sales.monitoring.sales_count'))};
    const esc=v=>String(v??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));
    const pin=(color,label)=>L.divIcon({className:'sales-pin-wrapper',iconSize:[40,50],iconAnchor:[20,49],popupAnchor:[0,-45],html:`<div class="sales-pin"><svg viewBox="0 0 40 50"><path d="M20 1C9.5 1 1 9.3 1 19.6C1 32.6 20 49 20 49s19-16.4 19-29.4C39 9.3 30.5 1 20 1Z" fill="${color}" stroke="#fff" stroke-width="2.5"/><circle cx="20" cy="19" r="10" fill="rgba(255,255,255,.15)"/></svg><span class="sales-pin-label">${esc(label)}</span></div>`});
    async function load(){try{const url=new URL(endpoint,window.location.origin);url.searchParams.set('date',date);if(employeeId)url.searchParams.set('employee_id',employeeId);const res=await fetch(url,{headers:{'Accept':'application/json'}});const data=await res.json();layers.forEach(l=>map.removeLayer(l));layers=[];const bounds=[];const list=document.getElementById('monitor-list');list.innerHTML='';(data.employees||[]).forEach((item,index)=>{if(!item.latest)return;const point=[item.latest.latitude,item.latest.longitude];const color=item.latest.is_moving?'#0f766e':'#64748b';const marker=L.marker(point,{icon:pin(color,String(index+1)),riseOnHover:true}).addTo(map).bindPopup(`<div style="min-width:220px"><div style="font-size:14px;font-weight:700">${esc(item.employee_name)}</div><div style="margin-top:6px;font-size:12px;color:#666">${labels.last}: ${esc(new Date(item.latest.recorded_at).toLocaleString())}</div><div style="font-size:12px;color:#666">${labels.accuracy}: ${item.latest.accuracy_meters??'-'} m</div><div style="font-size:12px;color:#666">${labels.battery}: ${item.latest.battery_level??'-'}%</div></div>`);layers.push(marker);bounds.push(point);if(item.points.length>1){const line=L.polyline(item.points.map(p=>[p.latitude,p.longitude]),{weight:3,opacity:.55,color:'#0f766e'}).addTo(map);layers.push(line);}list.insertAdjacentHTML('beforeend',`<button type="button" data-lat="${point[0]}" data-lng="${point[1]}" class="block w-full border-b border-black/[0.05] px-5 py-4 text-left hover:bg-[#faf9f6]"><div class="flex items-center justify-between gap-3"><div><div class="text-sm font-semibold">${esc(item.employee_name)}</div><div class="mt-1 text-xs text-[#85827b]">${esc(item.branch||'—')} · ${item.points.length} ${labels.points}</div></div><span class="rounded-full px-3 py-1.5 text-xs font-semibold ${item.latest.is_moving?'bg-teal-50 text-teal-700':'bg-slate-100 text-slate-600'}">${item.latest.is_moving?labels.moving:labels.idle}</span></div></button>`);});list.querySelectorAll('[data-lat]').forEach(btn=>btn.addEventListener('click',()=>map.setView([Number(btn.dataset.lat),Number(btn.dataset.lng)],17)));document.getElementById('monitor-summary').textContent=`${(data.employees||[]).length} ${labels.sales} · ${data.total_points||0} ${labels.points}`;if(bounds.length)map.fitBounds(bounds,{padding:[50,50],maxZoom:16});else map.setView([-2.5489,118.0149],5);map.invalidateSize();}catch(e){console.error(e);}}
    load(); setInterval(load,5000); window.addEventListener('resize',()=>map.invalidateSize());
})();
</script>
@endpush
