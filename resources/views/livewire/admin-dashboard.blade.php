<?php

use Livewire\Component;
use App\Models\CatalogItem;
use App\Models\Customer;
use App\Models\RawMaterial;
use App\Models\PatientOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

new class extends Component
{
    // Estado del CRUD de Productos
    public $isModalOpen = false;
    public $editingProductId = null;
    
    // Campos del formulario CRUD Productos
    public $name = '';
    public $brand = '';
    public $health_category = '';
    public $presentation_type = '';
    public $price_public = '';
    public $stock = 0;
    public $weight_volume = '';
    public $benefits = '';
    public $lot_number = '';
    public $manufactured_at = '';
    public $expires_at = '';
    public $shelf_location = '';
    public $searchProduct = '';

    // Estado del Modal de Venta Presencial (POS / En Local)
    public $isPosModalOpen = false;
    public $pos_customer_phone = '';
    public $pos_customer_name = '';
    public $pos_customer_dni = '';
    public $pos_customer_age = '';
    public $pos_health_interest = '';
    public $pos_cart = []; // ['product_id' => ['name', 'price', 'qty']]
    public $pos_selected_product_id = '';
    public $pos_payment_method = 'Efectivo'; // Efectivo, Yape/Plin, Tarjeta

    // Métodos CRUD Productos
    public function openModal()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingProductId = null;
        $this->name = '';
        $this->brand = '';
        $this->health_category = '';
        $this->presentation_type = '';
        $this->price_public = '';
        $this->stock = 0;
        $this->weight_volume = '';
        $this->benefits = '';
        $this->lot_number = '';
        $this->manufactured_at = '';
        $this->expires_at = '';
        $this->shelf_location = '';
    }

    public function saveProduct()
    {
        $this->validate([
            'name' => 'required|min:3',
            'health_category' => 'required',
            'presentation_type' => 'required|string|max:255',
            'weight_volume' => 'required|string|max:255',
            'price_public' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'lot_number' => 'nullable|string|max:255',
            'manufactured_at' => 'nullable|date',
            'expires_at' => ['nullable', 'date', ...($this->manufactured_at ? ['after_or_equal:manufactured_at'] : [])],
            'shelf_location' => 'nullable|string|max:255',
        ]);

        CatalogItem::updateOrCreate(
            ['id' => $this->editingProductId],
            [
                'name' => $this->name,
                'brand' => $this->brand ?: 'Carmelitas',
                'health_category' => $this->health_category,
                'presentation_type' => $this->presentation_type,
                'price_public' => $this->price_public,
                'stock' => $this->stock,
                'weight_volume' => $this->weight_volume,
                'benefits' => $this->benefits,
                'lot_number' => $this->lot_number ?: null,
                'manufactured_at' => $this->manufactured_at ?: null,
                'expires_at' => $this->expires_at ?: null,
                'shelf_location' => $this->shelf_location ?: null,
            ]
        );

        session()->flash('message', $this->editingProductId ? 'Producto actualizado correctamente.' : 'Producto creado exitosamente.');
        $this->closeModal();
    }

    public function editProduct($id)
    {
        $product = CatalogItem::findOrFail($id);
        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->brand = $product->brand;
        $this->health_category = $product->health_category;
        $this->presentation_type = $product->presentation_type;
        $this->price_public = $product->price_public;
        $this->stock = $product->stock;
        $this->weight_volume = $product->weight_volume;
        $this->benefits = $product->benefits;
        $this->lot_number = $product->lot_number ?? '';
        $this->manufactured_at = $product->manufactured_at?->format('Y-m-d') ?? '';
        $this->expires_at = $product->expires_at?->format('Y-m-d') ?? '';
        $this->shelf_location = $product->shelf_location ?? '';

        $this->isModalOpen = true;
    }

    public function deleteProduct($id)
    {
        $product = CatalogItem::findOrFail($id);

        if ($product->orderItems()->exists()) {
            session()->flash('message', 'No se puede eliminar un producto que aparece en pedidos. Puedes dejar su stock en cero.');
            return;
        }

        $product->delete();
        session()->flash('message', 'Producto eliminado.');
    }

    // LÓGICA DE VENTA EN LOCAL (POS)
    public function openPosModal()
    {
        $this->resetPos();
        $this->isPosModalOpen = true;
    }

    public function closePosModal()
    {
        $this->isPosModalOpen = false;
        $this->resetPos();
    }

    public function resetPos()
    {
        $this->pos_customer_phone = '';
        $this->pos_customer_name = '';
        $this->pos_customer_dni = '';
        $this->pos_customer_age = '';
        $this->pos_health_interest = '';
        $this->pos_cart = [];
        $this->pos_selected_product_id = '';
        $this->pos_payment_method = 'Efectivo';
    }

    public function searchCustomerByPhone()
    {
        if (strlen($this->pos_customer_phone) >= 8) {
            $customer = Customer::where('phone', $this->pos_customer_phone)->first();
            if ($customer) {
                $this->pos_customer_name = $customer->name;
                $this->pos_customer_dni = $customer->dni;
                $this->pos_customer_age = $customer->age;
                $this->pos_health_interest = $customer->health_interest;
            }
        }
    }

    public function addProductToPosCart()
    {
        if (!$this->pos_selected_product_id) {
            return;
        }

        $product = CatalogItem::find($this->pos_selected_product_id);
        if (!$product || $product->stock <= 0 || $product->expires_at?->isBefore(today())) {
            session()->flash('pos_error', 'Producto sin stock suficiente.');
            return;
        }

        if (isset($this->pos_cart[$product->id])) {
            if ($this->pos_cart[$product->id]['qty'] < $product->stock) {
                $this->pos_cart[$product->id]['qty']++;
            }
        } else {
            $this->pos_cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price_public,
                'qty' => 1,
            ];
        }

        $this->pos_selected_product_id = '';
    }

    public function removeProductFromPosCart($id)
    {
        unset($this->pos_cart[$id]);
    }

    public function getPosCartTotalProperty(): float
    {
        return (float) collect(is_array($this->pos_cart) ? $this->pos_cart : [])->sum(function ($item): float {
            if (!is_array($item)) {
                return 0.0;
            }

            $product = CatalogItem::find($item['id'] ?? null);

            return $product ? (float) $product->price_public * (int) ($item['qty'] ?? 0) : 0.0;
        });
    }

    public function processPosSale()
    {
        if (empty($this->pos_cart)) {
            session()->flash('pos_error', 'Debe agregar al menos un producto a la venta.');
            return;
        }

        $validated = $this->validate([
            'pos_customer_name' => 'required|min:3',
            'pos_customer_phone' => 'required|digits_between:8,11',
            'pos_customer_dni' => 'nullable|digits:8',
            'pos_customer_age' => 'nullable|integer|min:1|max:120',
            'pos_health_interest' => 'nullable|string|max:255',
            'pos_payment_method' => 'required|in:Efectivo,Yape / Plin,Tarjeta (POS)',
            'pos_cart' => 'required|array|min:1',
            'pos_cart.*.id' => 'required|integer|distinct',
            'pos_cart.*.qty' => 'required|integer|min:1|max:1000',
        ]);

        $sale = DB::transaction(function () use ($validated): array {
            $saleItems = [];
            $total = 0.0;

            foreach ($validated['pos_cart'] as $item) {
                $product = CatalogItem::query()->whereKey($item['id'])->lockForUpdate()->first();

                if (!$product || $product->stock < $item['qty'] || $product->expires_at?->isBefore(today())) {
                    throw ValidationException::withMessages([
                        'pos_cart' => 'Uno o más productos ya no tienen stock suficiente.',
                    ]);
                }

                $unitPrice = (float) $product->price_public;
                $total += $unitPrice * $item['qty'];
                $saleItems[] = [
                    'catalog_item_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['qty'],
                    'unit_price' => $unitPrice,
                ];
            }

            $customer = Customer::updateOrCreate(
                ['phone' => $validated['pos_customer_phone']],
                [
                    'name' => $validated['pos_customer_name'],
                    'dni' => $validated['pos_customer_dni'] ?: null,
                    'age' => $validated['pos_customer_age'] ?: null,
                    'city' => 'Cusco',
                    'health_interest' => $validated['pos_health_interest'] ?? null,
                    'last_purchase_date' => Carbon::today(),
                    'treatment_status' => 'En Curso',
                    'treatment_start_date' => Carbon::today(),
                    'treatment_end_date' => Carbon::today()->addMonths(3),
                ]
            );

            if ($customer->wasRecentlyCreated) {
                $customer->update(['visit_count' => 1]);
            } else {
                $customer->increment('visit_count');
            }
            $order = PatientOrder::create([
                'customer_id' => $customer->id,
                'total_amount' => $total,
                'channel' => 'Tienda Física',
                'payment_method' => $validated['pos_payment_method'],
                'status' => 'Completado',
            ]);

            foreach ($saleItems as $saleItem) {
                $order->items()->create($saleItem);
                CatalogItem::whereKey($saleItem['catalog_item_id'])->decrement('stock', $saleItem['quantity']);
            }

            return ['order' => $order, 'total' => $total];
        });

        session()->flash('message', '✅ Venta #' . $sale['order']->id . ' registrada (S/ ' . number_format($sale['total'], 2) . '). Stock actualizado.');
        $this->closePosModal();
    }

    public function with()
    {
        // 1. Métricas generales
        $totalSalesMonth = class_exists(PatientOrder::class)
            ? PatientOrder::where('status', 'Completado')
                ->whereHas('customer', fn ($query) => $query->where('is_demo', false))
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('total_amount')
            : 0;

        $activePatientsCount = Customer::where('is_demo', false)->where('treatment_status', 'En Curso')->count();
        $totalPatientsCount = Customer::where('is_demo', false)->count();

        // 2. Alertas
        $lowStockProducts = CatalogItem::where('stock', '<=', 10)->get();
        $expiringProducts = CatalogItem::whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', Carbon::today()->addDays(30))
            ->orderBy('expires_at')
            ->get();
        $lowStockRawMaterials = class_exists(RawMaterial::class) 
            ? RawMaterial::whereRaw('stock_qty <= min_stock_alert')->get() 
            : collect([]);

        // 3. Seguimiento
        $activePatients = Customer::where('is_demo', false)
            ->where('treatment_status', 'En Curso')
            ->orderBy('treatment_end_date', 'asc')
            ->get()
            ->map(function ($patient) {
                $patient->days_remaining = (int) Carbon::today()->diffInDays(Carbon::parse($patient->treatment_end_date)->startOfDay(), false);
                return $patient;
            });

        // 4. Últimas órdenes
        $recentOrders = class_exists(PatientOrder::class)
            ? PatientOrder::with(['customer', 'items'])->latest()->take(5)->get()
            : collect([]);

        $customerHistories = Customer::withCount('orders')
            ->withSum('orders', 'total_amount')
            ->with(['orders' => fn ($query) => $query->latest()->with('items')])
            ->orderByDesc('is_demo')
            ->orderByDesc('last_purchase_date')
            ->take(10)
            ->get();

        $topProducts = DB::table('patient_order_items')
            ->join('patient_orders', 'patient_orders.id', '=', 'patient_order_items.patient_order_id')
            ->join('customers', 'customers.id', '=', 'patient_orders.customer_id')
            ->where('customers.is_demo', false)
            ->where('patient_orders.status', '!=', 'Cancelado')
            ->select('patient_order_items.product_name', DB::raw('SUM(patient_order_items.quantity) as units'), DB::raw('COUNT(DISTINCT patient_orders.id) as order_count'))
            ->groupBy('patient_order_items.product_name')
            ->orderByDesc('units')
            ->limit(5)
            ->get();

        // 5. Lista de productos para el CRUD
        $crudProducts = CatalogItem::where('name', 'like', '%' . $this->searchProduct . '%')
            ->orWhere('health_category', 'like', '%' . $this->searchProduct . '%')
            ->orWhere('brand', 'like', '%' . $this->searchProduct . '%')
            ->orderBy('id', 'desc')
            ->get();

        // 6. Datos para Gráfico de Ventas Mensuales (Últimos 6 meses)
        $monthlySalesData = [];
        $monthlyLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $date->translatedFormat('F');
            $monthlySalesData[] = PatientOrder::where('status', 'Completado')
                ->whereHas('customer', fn ($query) => $query->where('is_demo', false))
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');
        }

        return [
            'totalSalesMonth' => $totalSalesMonth,
            'activePatientsCount' => $activePatientsCount,
            'totalPatientsCount' => $totalPatientsCount,
            'lowStockProducts' => $lowStockProducts,
            'expiringProducts' => $expiringProducts,
            'lowStockRawMaterials' => $lowStockRawMaterials,
            'activePatients' => $activePatients,
            'recentOrders' => $recentOrders,
            'customerHistories' => $customerHistories,
            'topProducts' => $topProducts,
            'crudProducts' => $crudProducts,
            'allCatalogProducts' => CatalogItem::where('stock', '>', 0)
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', Carbon::today()))
                ->get(),
            'monthlyLabels' => $monthlyLabels,
            'monthlySalesData' => $monthlySalesData,
        ];
    }
};
?>

<div>
    <!-- CHART.JS VIA CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .admin-dashboard-shell { max-width: 1440px !important; padding: clamp(14px, 3vw, 32px) !important; background: #f4f7f1 !important; color: #27352b; }
        .dashboard-header { padding: 22px 24px; border-radius: 18px; background: linear-gradient(120deg, #fff 0%, #edf6e8 100%); box-shadow: 0 10px 28px rgb(37 79 40 / 9%); }
        .dashboard-header h1 { font-size: clamp(21px, 3vw, 30px) !important; letter-spacing: -.03em; }
        .dashboard-header p { font-size: 14px !important; }
        .dashboard-metrics > div, .dashboard-panel { border: 1px solid #e5ece2; border-radius: 14px !important; box-shadow: 0 8px 22px rgb(36 65 37 / 6%) !important; }
        .dashboard-metrics > div { min-height: 104px; }
        .dashboard-panel h2 { padding-bottom: 12px !important; }
        .dashboard-table tbody tr:hover { background: #f6faf3; }
        .dashboard-table th { font-size: 11px; letter-spacing: .04em; text-transform: uppercase; }
        .dashboard-button { transition: transform .15s ease, box-shadow .15s ease; }
        .dashboard-button:hover { transform: translateY(-1px); box-shadow: 0 5px 12px rgb(25 60 30 / 16%); }
        .customer-history-grid { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(230px, .75fr); gap: 18px; margin: 0 0 24px; }
        .customer-history-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .customer-history-table th, .customer-history-table td { padding: 11px 10px; border-bottom: 1px solid #edf0eb; text-align: left; vertical-align: top; }
        .customer-history-table th { color: #53704f; background: #f4f8f1; font-size: 11px; text-transform: uppercase; }
        .demo-badge { display: inline-block; padding: 2px 7px; border-radius: 999px; color: #695000; background: #fff3c2; font-size: 10px; font-weight: 700; }
        .history-order { margin-bottom: 6px; color: #606c5f; font-size: 11px; }
        .top-product { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-bottom: 1px solid #edf0eb; font-size: 13px; }
        @media (max-width: 760px) { .customer-history-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="admin-dashboard-shell" style="font-family: Arial, sans-serif; padding: 20px; max-width: 1300px; margin: auto; background-color: #f8f9fa;">
        
        <!-- HEADER -->
        <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h1 style="color: #1b5e20; margin: 0; font-size: 24px;">📊 Panel de Control & Ventas - Bio Natural Carmelita</h1>
                <p style="color: #666; margin: 5px 0 0 0; font-size: 13px;">Gestión de inventario, ventas presenciales, catálogo y estadísticas en Cusco.</p>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <!-- BOTÓN NUEVA VENTA EN LOCAL -->
                <button wire:click="openPosModal" style="background: #e65100; color: white; border: none; padding: 10px 18px; border-radius: 6px; font-weight: bold; font-size: 13px; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
                    🛍️ Nueva Venta en Local (POS)
                </button>

                <a href="/" target="_blank" style="background: #2e7d32; color: white; padding: 10px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 13px;">
                    👁️ Ver Catálogo Web
                </a>
            </div>
        </div>

        @if(session()->has('message'))
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold;">
                {{ session('message') }}
            </div>
        @endif

        <!-- TARJETAS DE MÉTRICAS -->
        <div class="dashboard-metrics" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 25px;">
            <div style="background: white; padding: 18px; border-radius: 8px; border-left: 5px solid #2e7d32; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; color: #777; font-weight: bold;">VENTAS DEL MES</div>
                <div style="font-size: 22px; font-weight: bold; color: #2e7d32; margin-top: 5px;">S/ {{ number_format($totalSalesMonth, 2) }}</div>
            </div>

            <div style="background: white; padding: 18px; border-radius: 8px; border-left: 5px solid #0288d1; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; color: #777; font-weight: bold;">CLIENTES EN SEGUIMIENTO</div>
                <div style="font-size: 22px; font-weight: bold; color: #0288d1; margin-top: 5px;">{{ $activePatientsCount }} / {{ $totalPatientsCount }}</div>
            </div>

            <div style="background: white; padding: 18px; border-radius: 8px; border-left: 5px solid #d32f2f; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; color: #777; font-weight: bold;">ALERTAS STOCK PRODUCTOS</div>
                <div style="font-size: 22px; font-weight: bold; color: #d32f2f; margin-top: 5px;">{{ count($lowStockProducts) }} Ítems</div>
            </div>

            <div style="background: white; padding: 18px; border-radius: 8px; border-left: 5px solid #ed6c02; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; color: #777; font-weight: bold;">MATERIA PRIMA CRÍTICA</div>
                <div style="font-size: 22px; font-weight: bold; color: #ed6c02; margin-top: 5px;">{{ count($lowStockRawMaterials) }} Insumos</div>
            </div>

            <div style="background: white; padding: 18px; border-radius: 8px; border-left: 5px solid #8e24aa; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; color: #777; font-weight: bold;">VENCIDOS / POR VENCER (30 DÍAS)</div>
                <div style="font-size: 22px; font-weight: bold; color: #8e24aa; margin-top: 5px;">{{ count($expiringProducts) }} Productos</div>
            </div>
        </div>

        @if($expiringProducts->isNotEmpty())
            <div role="alert" style="background: #fff3e0; color: #7a3e00; border: 1px solid #ffcc80; border-left: 5px solid #ed6c02; border-radius: 8px; padding: 14px 18px; margin: -10px 0 25px;">
                <strong>⚠️ Revisar lotes vencidos o próximos a vencer:</strong>
                <ul style="margin: 8px 0 0; padding-left: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 4px 20px;">
                    @foreach($expiringProducts as $expiringProduct)
                        <li>{{ $expiringProduct->name }} — lote {{ $expiringProduct->lot_number ?: 'sin lote' }} — vence {{ $expiringProduct->expires_at->format('d/m/Y') }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- SECCIÓN DE GRÁFICOS Y ANALÍTICA DE VENTAS -->
        <div class="dashboard-panel" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px;">
            <h2 style="font-size: 16px; color: #1b5e20; border-bottom: 2px solid #e8f5e9; padding-bottom: 8px; margin-top: 0;">
                📈 Reporte & Estadísticas de Ventas
            </h2>
            <div style="height: 280px; width: 100%;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- SECCIÓN CRUD DE PRODUCTOS -->
        <div class="dashboard-panel" id="dashboard-inventario" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e8f5e9; padding-bottom: 12px; margin-bottom: 15px;">
                <h2 style="font-size: 18px; color: #1b5e20; margin: 0;">📦 Inventario & Catálogo de Productos (CRUD)</h2>
                <button wire:click="openModal" style="background: #2e7d32; color: white; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 13px;">
                    + Nuevo Producto
                </button>
            </div>

            <!-- Buscador interno -->
            <div style="margin-bottom: 15px;">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="searchProduct" 
                    placeholder="Filtrar por nombre, categoría o marca en almacén..." 
                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                >
            </div>

            <!-- TABLA PRODUCTOS -->
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="background: #f1f8e9; color: #2e7d32; border-bottom: 2px solid #c8e6c9;">
                            <th style="padding: 10px;">ID</th>
                            <th style="padding: 10px;">Producto</th>
                            <th style="padding: 10px;">Categoría Salud</th>
                            <th style="padding: 10px;">Marca / Presentación</th>
                            <th style="padding: 10px;">Precio (S/)</th>
                            <th style="padding: 10px;">Stock Actual</th>
                            <th style="padding: 10px;">Lote / Fechas</th>
                            <th style="padding: 10px; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($crudProducts as $prod)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px; color: #888; font-weight: bold;">#{{ $prod->id }}</td>
                                <td style="padding: 10px; font-weight: bold; color: #333;">
                                    {{ $prod->name }}
                                    @if($prod->benefits)
                                        <div style="font-size: 11px; color: #666; font-weight: normal;">{{ Str::limit($prod->benefits, 50) }}</div>
                                    @endif
                                </td>
                                <td style="padding: 10px;">
                                    <span style="background: #e8f5e9; color: #2e7d32; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                                        {{ $prod->health_category }}
                                    </span>
                                </td>
                                <td style="padding: 10px; color: #555;">{{ $prod->brand }} | {{ $prod->weight_volume }}</td>
                                <td style="padding: 10px; font-weight: bold; color: #1b5e20;">S/ {{ number_format($prod->price_public, 2) }}</td>
                                <td style="padding: 10px;">
                                    <span style="font-weight: bold; padding: 3px 8px; border-radius: 4px; {{ $prod->stock <= 10 ? 'background: #ffebee; color: #c62828;' : 'background: #e8f5e9; color: #2e7d32;' }}">
                                        {{ $prod->stock }} unids.
                                    </span>
                                </td>
                                <td style="padding: 10px; min-width: 150px; font-size: 11px; color: #555;">
                                    <div><strong>Ubicación:</strong> {{ $prod->shelf_location ?: '—' }}</div>
                                    <div><strong>Lote:</strong> {{ $prod->lot_number ?: '—' }}</div>
                                    <div><strong>Fabricación:</strong> {{ $prod->manufactured_at?->format('d/m/Y') ?? '—' }}</div>
                                    <div style="color: {{ $prod->expires_at && $prod->expires_at->isBefore(today()) ? '#c62828' : ($prod->expires_at && $prod->expires_at->lte(today()->addDays(30)) ? '#ed6c02' : '#555') }};">
                                        <strong>Caducidad:</strong> {{ $prod->expires_at?->format('d/m/Y') ?? '—' }}
                                        @if($prod->expires_at && $prod->expires_at->isBefore(today())) (Vencido)
                                        @elseif($prod->expires_at && $prod->expires_at->lte(today()->addDays(30))) (Por vencer)
                                        @endif
                                    </div>
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    <button wire:click="editProduct({{ $prod->id }})" style="background: #0288d1; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: bold; margin-right: 4px;">
                                        Editar
                                    </button>
                                    <button wire:confirm="¿Seguro que deseas eliminar este producto?" wire:click="deleteProduct({{ $prod->id }})" style="background: #d32f2f; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: bold;">
                                        Borrar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="padding: 20px; text-align: center; color: #888;">No se encontraron productos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL NUEVA VENTA EN LOCAL (POS) -->
        @if($isPosModalOpen)
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 99999;">
                <div style="background: white; padding: 25px; border-radius: 12px; width: 90%; max-width: 600px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e65100; padding-bottom: 10px; margin-bottom: 15px;">
                        <h3 style="margin: 0; color: #e65100; font-size: 18px;">🛍️ Registrar Venta Presencial (En Local)</h3>
                        <button wire:click.prevent="closePosModal" style="background: none; border: none; font-size: 18px; cursor: pointer;">✖</button>
                    </div>

                    @if(session()->has('pos_error'))
                        <div style="background: #ffebee; color: #c62828; padding: 8px; border-radius: 4px; font-size: 12px; margin-bottom: 12px;">
                            {{ session('pos_error') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="processPosSale">
                        <!-- BÚSQUEDA / DATOS DEL CLIENTE -->
                        <div style="background: #fdf7f2; padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #ffe0b2;">
                            <span style="font-size: 12px; font-weight: bold; color: #e65100; display: block; margin-bottom: 8px;">👤 Datos del Cliente / Paciente</span>
                            
                            <div style="display: flex; gap: 10px; margin-bottom: 8px;">
                                <div style="flex: 1;">
                                    <input type="text" wire:model.live="pos_customer_phone" wire:keyup="searchCustomerByPhone" placeholder="Celular / WhatsApp *" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 12px;">
                                    @error('pos_customer_phone') <span style="color: red; font-size: 11px;">{{ $message }}</span> @enderror
                                </div>
                                <div style="flex: 1.5;">
                                    <input type="text" wire:model.live="pos_customer_name" placeholder="Nombre Completo *" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 12px;">
                                    @error('pos_customer_name') <span style="color: red; font-size: 11px;">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px;">
                                <input type="text" wire:model.live="pos_customer_dni" placeholder="DNI" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 12px;">
                                <input type="number" wire:model.live="pos_customer_age" placeholder="Edad" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 12px;">
                                <input type="text" wire:model.live="pos_health_interest" placeholder="Afección (Ej: Gastritis)" style="flex: 1.5; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 12px;">
                            </div>
                        </div>

                        <!-- AGREGAR PRODUCTOS A LA VENTA -->
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Seleccionar Producto del Inventario:</label>
                            <div style="display: flex; gap: 8px;">
                                <select wire:model="pos_selected_product_id" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;">
                                    <option value="">-- Buscar producto con stock --</option>
                                    @foreach($allCatalogProducts as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} (S/ {{ number_format($p->price_public, 2) }}) - Stock: {{ $p->stock }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="addProductToPosCart" style="background: #2e7d32; color: white; border: none; padding: 8px 14px; border-radius: 4px; font-weight: bold; cursor: pointer;">
                                    + Agregar
                                </button>
                            </div>
                        </div>

                        <!-- DETALLE DEL CARRITO POS -->
                        <div style="margin-bottom: 15px; border: 1px solid #eee; border-radius: 6px; padding: 10px;">
                            <span style="font-size: 12px; font-weight: bold; color: #555;">🛒 Productos en la Orden:</span>
                            @forelse($pos_cart as $item)
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #f0f0f0; font-size: 12px;">
                                    <span><strong>{{ $item['name'] }}</strong> (x{{ $item['qty'] }})</span>
                                    <div>
                                        <span style="font-weight: bold; color: #2e7d32; margin-right: 10px;">S/ {{ number_format($item['price'] * $item['qty'], 2) }}</span>
                                        <button type="button" wire:click="removeProductFromPosCart({{ $item['id'] }})" style="background: #ffebee; color: red; border: none; border-radius: 3px; cursor: pointer; padding: 2px 6px;">✖</button>
                                    </div>
                                </div>
                            @empty
                                <p style="font-size: 12px; color: #999; text-align: center; margin: 10px 0;">No se han añadido productos aún.</p>
                            @endforelse

                            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 15px; margin-top: 10px; color: #1b5e20;">
                                <span>TOTAL A COBRAR:</span>
                                <span>S/ {{ number_format($this->posCartTotal, 2) }}</span>
                            </div>
                        </div>

                        <!-- MÉTODO DE PAGO -->
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px;">Método de Pago:</label>
                            <select wire:model.live="pos_payment_method" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Yape / Plin">Yape / Plin</option>
                                <option value="Tarjeta (POS)">Tarjeta de Débito / Crédito</option>
                            </select>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee; padding-top: 12px;">
                            <button type="button" wire:click.prevent="closePosModal" style="background: #757575; color: white; border: none; padding: 10px 16px; border-radius: 6px; font-weight: bold; font-size: 12px; cursor: pointer;">
                                Cancelar
                            </button>
                            <button type="submit" style="background: #e65100; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; font-size: 13px; cursor: pointer;">
                                💳 Completar Venta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- MODAL CREAR / EDITAR PRODUCTO -->
        @if($isModalOpen)
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 99999;">
                <div style="background: white; padding: 25px; border-radius: 12px; width: 90%; max-width: 520px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">
                    <h3 style="margin-top: 0; color: #1b5e20; border-bottom: 2px solid #e8f5e9; padding-bottom: 10px; font-size: 18px;">
                        {{ $editingProductId ? '✏️ Editar Producto' : '➕ Crear Nuevo Producto' }}
                    </h3>

                    <form wire:submit.prevent="saveProduct">
                        <div style="margin-bottom: 12px;">
                            <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; color: #333;">Nombre del Producto *</label>
                            <input type="text" wire:model.live="name" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 13px;">
                            @error('name') <span style="color: #d32f2f; font-size: 11px; font-weight: bold;">{{ $message }}</span> @enderror
                        </div>

                        <div style="display: flex; gap: 10px; margin-bottom: 12px;">
                            <div style="flex: 1;">
                                <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; color: #333;">Categoría Salud *</label>
                                <input type="text" wire:model.live="health_category" placeholder="ej: Gastritis, Próstata" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 13px;">
                                @error('health_category') <span style="color: #d32f2f; font-size: 11px; font-weight: bold;">{{ $message }}</span> @enderror
                            </div>
                            <div style="flex: 1;">
                                <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; color: #333;">Marca</label>
                                <input type="text" wire:model.live="brand" placeholder="Carmelitas" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 13px;">
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; color: #333;">Tipo de Presentación *</label>
                            <input type="text" wire:model.live="presentation_type" placeholder="ej: Bebida concentrada, cápsulas" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 13px;">
                            @error('presentation_type') <span style="color: #d32f2f; font-size: 11px; font-weight: bold;">{{ $message }}</span> @enderror
                        </div>

                        <div style="display: flex; gap: 10px; margin-bottom: 12px;">
                            <div style="flex: 1;">
                                <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; color: #333;">Precio Público (S/) *</label>
                                <input type="number" step="0.01" wire:model.live="price_public" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 13px;">
                                @error('price_public') <span style="color: #d32f2f; font-size: 11px; font-weight: bold;">{{ $message }}</span> @enderror
                            </div>
                            <div style="flex: 1;">
                                <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; color: #333;">Stock Actual *</label>
                                <input type="number" wire:model.live="stock" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 13px;">
                                @error('stock') <span style="color: #d32f2f; font-size: 11px; font-weight: bold;">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div style="margin-bottom: 12px;">
                            <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; color: #333;">Presentación / Peso / Volumen</label>
                            <input type="text" wire:model.live="weight_volume" placeholder="ej: 100g, 500ml" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 13px;">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; color: #333;">Beneficios / Descripción Corta</label>
                            <textarea wire:model.live="benefits" rows="3" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 13px; font-family: inherit;"></textarea>
                        </div>

                        <div style="border-top: 1px solid #e5ece2; padding-top: 14px; margin-bottom: 16px;">
                            <h4 style="margin: 0 0 12px; color: #1b5e20;">Trazabilidad del producto</h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
                                <div>
                                    <label for="lot_number" style="display: block; font-size: 12px; font-weight: bold; color: #555; margin-bottom: 5px;">Número de lote</label>
                                    <input id="lot_number" type="text" wire:model="lot_number" maxlength="255" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;">
                                    @error('lot_number') <span style="color: #c62828; font-size: 11px;">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="manufactured_at" style="display: block; font-size: 12px; font-weight: bold; color: #555; margin-bottom: 5px;">Fecha de fabricación</label>
                                    <input id="manufactured_at" type="date" wire:model="manufactured_at" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;">
                                    @error('manufactured_at') <span style="color: #c62828; font-size: 11px;">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="expires_at" style="display: block; font-size: 12px; font-weight: bold; color: #555; margin-bottom: 5px;">Fecha de caducidad</label>
                                    <input id="expires_at" type="date" wire:model="expires_at" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;">
                                    @error('expires_at') <span style="color: #c62828; font-size: 11px;">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div style="margin-top: 12px;">
                                <label for="shelf_location" style="display: block; font-size: 12px; font-weight: bold; color: #555; margin-bottom: 5px;">Ubicación / estante</label>
                                <input id="shelf_location" type="text" wire:model="shelf_location" maxlength="255" placeholder="Ej.: Estante A-01, nivel 2" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;">
                                @error('shelf_location') <span style="color: #c62828; font-size: 11px;">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee; padding-top: 15px;">
                            <button type="button" wire:click.prevent="closeModal" style="background: #757575; color: white; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 13px;">
                                Cancelar
                            </button>
                            <button type="submit" style="background: #2e7d32; color: white; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 13px;">
                                Guardar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <p style="margin: 0 0 12px; padding: 10px 13px; border: 1px solid #f1dfa0; border-radius: 9px; background: #fff9df; color: #695000; font-size: 12px;">
            Los clientes marcados como DEMO son ficticios; sus pedidos se excluyen de ventas y demanda reales.
        </p>
        <section id="dashboard-clientes" class="customer-history-grid">
            <div class="dashboard-panel" style="min-width: 0; background: white; padding: 20px;">
                <h2 style="margin: 0 0 14px; color: #1b5e20; font-size: 17px;">👥 Clientes e historial de compras</h2>
                <div style="overflow-x: auto;">
                    <table class="customer-history-table">
                        <thead><tr><th>Cliente</th><th>Pedidos</th><th>Compras recientes</th></tr></thead>
                        <tbody>
                            @forelse($customerHistories as $client)
                                <tr>
                                    <td><strong>{{ $client->name }}</strong>
                                        @if($client->is_demo)<span class="demo-badge">DEMO</span>@endif
                                        <br><small style="color: #687468;">{{ $client->phone }}</small>
                                    </td>
                                    <td>{{ $client->orders_count }}<br><small style="color: #687468;">S/ {{ number_format($client->orders_sum_total_amount ?? 0, 2) }}</small></td>
                                    <td>
                                        @forelse($client->orders->take(2) as $historyOrder)
                                            <div class="history-order">
                                                <strong>{{ $historyOrder->created_at->format('d/m/Y') }}</strong> · {{ $historyOrder->status }} · S/ {{ number_format($historyOrder->total_amount, 2) }}
                                                <br>{{ $historyOrder->items->map(fn ($item) => ($item->product_name ?? 'Producto').' × '.$item->quantity)->implode(', ') }}
                                            </div>
                                        @empty
                                            <span style="color: #899188;">Sin pedidos registrados</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align: center; color: #899188;">Todavía no hay clientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="dashboard-panel" style="background: white; padding: 20px;">
                <h2 style="margin: 0 0 4px; color: #1b5e20; font-size: 17px;">📦 Más solicitados</h2>
                <p style="margin: 0 0 8px; color: #7a8578; font-size: 11px;">Pedidos reales; excluye datos demo.</p>
                @forelse($topProducts as $product)
                    <div class="top-product"><span>{{ $product->product_name }}<br><small style="color: #7a8578;">{{ $product->order_count }} pedido(s)</small></span><strong>{{ $product->units }} u.</strong></div>
                @empty
                    <p style="color: #899188; font-size: 13px;">Aún no hay pedidos reales.</p>
                @endforelse
            </aside>
        </section>

        <!-- TABLAS SECUNDARIAS DE PACIENTES Y VENTAS -->
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div style="flex: 3; min-width: 340px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h2 style="font-size: 16px; color: #1b5e20; border-bottom: 2px solid #e8f5e9; padding-bottom: 8px; margin-top: 0;">
                    🩺 Pacientes en Tratamiento Activo (Ciclo de 3 Meses)
                </h2>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="background: #f1f8e9; color: #33691e;">
                            <th style="padding: 8px;">Paciente</th>
                            <th style="padding: 8px;">DNI / Edad</th>
                            <th style="padding: 8px; text-align: center;">Visitas</th>
                            <th style="padding: 8px;">Término</th>
                            <th style="padding: 8px;">Estado / Días Restantes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activePatients as $patient)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px; font-weight: bold;">
                                    {{ $patient->name }}<br>
                                    <small style="color:#777;">📱 {{ $patient->phone }}</small>
                                    <br><small style="color:#53704f;">{{ $patient->city }} · {{ $patient->health_interest }}</small>
                                </td>
                                <td style="padding: 8px;">
                                    DNI: {{ $patient->dni ?? 'S/N' }}<br>
                                    <small style="color: #555;">{{ $patient->age ? $patient->age . ' años' : '' }}</small>
                                </td>
                                <td style="padding: 8px; text-align: center;">
                                    <span style="background: #e0f2f1; color: #00695c; padding: 2px 8px; border-radius: 12px; font-weight: bold; font-size: 11px;">
                                        👁️ {{ $patient->visit_count ?? 1 }}
                                    </span>
                                </td>
                                <td style="padding: 8px;">
                                    {{ $patient->treatment_end_date?->format('d/m/Y') ?? 'Sin fecha' }}<br>
                                    <small style="color: #777;">Inicio: {{ $patient->treatment_start_date?->format('d/m/Y') ?? 'Sin fecha' }}</small>
                                </td>
                                <td style="padding: 8px;">
                                    @if($patient->days_remaining <= 7 && $patient->days_remaining >= 0)
                                        <span style="background: #ffebee; color: #c62828; padding: 3px 8px; border-radius: 4px; font-weight: bold;">
                                            🔔 Vence en {{ $patient->days_remaining }} días
                                        </span>
                                    @elseif($patient->days_remaining < 0)
                                        <span style="background: #efebe9; color: #4e342e; padding: 3px 8px; border-radius: 4px;">Finalizado</span>
                                    @else
                                        <span style="background: #e8f5e9; color: #2e7d32; padding: 3px 8px; border-radius: 4px;">En curso ({{ $patient->days_remaining }} días)</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 15px; text-align: center; color: #999;">No hay pacientes registrados con tratamientos en curso.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="flex: 2; min-width: 300px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h2 style="font-size: 16px; color: #1b5e20; border-bottom: 2px solid #e8f5e9; padding-bottom: 8px; margin-top: 0;">
                    🛒 Últimas Ventas
                </h2>
                <div style="max-height: 400px; overflow-y: auto;">
                    @forelse($recentOrders as $order)
                        <div style="padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                                <span>{{ $order->customer->name ?? 'Cliente General' }} @if($order->customer?->is_demo)<span class="demo-badge">DEMO</span>@endif</span>
                                <span style="color: #2e7d32;">S/ {{ number_format($order->total_amount ?? 0, 2) }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; color: #777; font-size: 11px; margin-top: 3px;">
                                <span>Canal: {{ $order->channel ?? 'Tienda Física' }}</span>
                                <span>{{ $order->status }} · {{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @foreach($order->items as $item)
                                <div style="color: #777; font-size: 11px; margin-top: 3px;">
                                    {{ $item->product_name ?? $item->catalogItem?->name ?? 'Producto' }} × {{ $item->quantity }}
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <p style="text-align: center; color: #999; padding: 20px;">Sin registros de ventas.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- SCRIPT INICIALIZADOR DE CHART.JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($monthlyLabels),
                    datasets: [{
                        label: 'Ventas Mensuales (S/)',
                        data: @json($monthlySalesData),
                        backgroundColor: 'rgba(46, 125, 50, 0.75)',
                        borderColor: '#1b5e20',
                        borderWidth: 2,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return 'S/ ' + value; }
                            }
                        }
                    }
                }
            });
        });
    </script>
</div>
