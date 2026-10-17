<?php

use Livewire\Component;
use App\Models\CatalogItem;
use App\Models\Customer;
use App\Models\PatientOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

new class extends Component
{
    public $cart = [];
    public $search = '';
    public $selectedCategory = '';
    
    // Control de modales y vistas
    public $showCartModal = false;
    public $showCustomerForm = false;

    // Datos del Paciente / Cliente
    public $customer_name = '';
    public $customer_dni = '';
    public $customer_phone = '';
    public $customer_age = '';
    public $customer_city = 'Cusco';
    public $customer_health_interest = '';

    public $whatsappNumber = '51929053458';

    public function addToCart($id)
    {
        $product = CatalogItem::findOrFail($id);

        if ($product->stock < 1 || $product->expires_at?->isBefore(today())) {
            session()->flash('cart_error', $product->expires_at?->isBefore(today()) ? 'Este producto está vencido y no se puede comprar.' : 'Este producto no tiene stock disponible.');
            return;
        }
        
        if (isset($this->cart[$id])) {
            if ($this->cart[$id]['qty'] < $product->stock) {
                $this->cart[$id]['qty']++;
            }
        } else {
            $this->cart[$id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price_public,
                'qty' => 1
            ];
        }
    }

    public function removeFromCart($id)
    {
        unset($this->cart[$id]);
    }

    public function getCartTotalProperty()
    {
        return collect(is_array($this->cart) ? $this->cart : [])->sum(function ($item): float {
            if (!is_array($item)) {
                return 0.0;
            }

            $product = CatalogItem::find($item['id'] ?? null);

            return $product ? (float) $product->price_public * (int) ($item['qty'] ?? 0) : 0.0;
        });
    }

    public function getCartCountProperty()
    {
        return collect(is_array($this->cart) ? $this->cart : [])->sum(
            fn ($item): int => is_array($item) ? (int) ($item['qty'] ?? 0) : 0,
        );
    }

    public function toggleCartModal()
    {
        $this->showCartModal = !$this->showCartModal;
        $this->showCustomerForm = false; // Reinicia vista
    }

    public function goToCustomerForm()
    {
        $this->showCustomerForm = true;
    }

    public function processCheckoutAndGetWhatsappUrl()
    {
        $validated = $this->validate([
            'customer_name' => 'required|string|min:3',
            'customer_phone' => 'required|numeric|digits_between:8,11',
            'customer_dni' => 'required|numeric|digits:8',
            'customer_age' => 'required|integer|min:1|max:120',
            'customer_city' => 'nullable|string|max:255',
            'customer_health_interest' => 'nullable|string|max:255',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer|distinct',
            'cart.*.qty' => 'required|integer|min:1|max:1000',
        ]);

        $checkout = DB::transaction(function () use ($validated): array {
            $items = [];
            $total = 0.0;

            foreach ($validated['cart'] as $item) {
                $product = CatalogItem::query()->whereKey($item['id'])->lockForUpdate()->first();

                if (!$product || $product->stock < $item['qty'] || $product->expires_at?->isBefore(today())) {
                    throw ValidationException::withMessages([
                        'cart' => 'Uno o más productos ya no tienen stock suficiente.',
                    ]);
                }

                $unitPrice = (float) $product->price_public;
                $total += $unitPrice * $item['qty'];
                $items[] = [
                    'catalog_item_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['qty'],
                    'unit_price' => $unitPrice,
                ];
            }

            $customer = Customer::updateOrCreate(
                ['phone' => $validated['customer_phone']],
                [
                    'name' => $validated['customer_name'],
                    'dni' => $validated['customer_dni'],
                    'age' => $validated['customer_age'],
                    'city' => $validated['customer_city'] ?: 'Cusco',
                    'health_interest' => $validated['customer_health_interest'] ?? null,
                ]
            );

            if ($customer->wasRecentlyCreated) {
                $customer->update(['visit_count' => 0]);
            }

            $order = PatientOrder::create([
                'customer_id' => $customer->id,
                'total_amount' => $total,
                'channel' => 'WhatsApp / Web',
                'status' => 'Pendiente',
            ]);

            $order->items()->createMany($items);

            return ['order' => $order, 'items' => $items, 'total' => $total];
        });

        // Formatear mensaje para WhatsApp
        $text = "Hola Bio Natural Carmelita 🌿, deseo realizar el siguiente pedido:\n\n";
        $text .= "👤 *Datos del Paciente:*\n";
        $text .= "• Nombre: {$validated['customer_name']}\n";
        $text .= "• DNI: {$validated['customer_dni']}\n";
        $text .= "• Edad: {$validated['customer_age']} años\n";
        $text .= "• Ciudad: {$validated['customer_city']}\n\n";

        $text .= "🛒 *Detalle del Pedido (#{$checkout['order']->id}):*\n";
        foreach ($checkout['items'] as $item) {
            $subtotal = $item['unit_price'] * $item['quantity'];
            $text .= "• *{$item['product_name']}* x{$item['quantity']} - S/ " . number_format($subtotal, 2) . "\n";
        }

        $text .= "\n*Total a Pagar:* S/ " . number_format($checkout['total'], 2) . "\n";
        $text .= "\nPor favor confirmen stock e instrucciones para completar mi pedido.";

        $url = "https://wa.me/" . $this->whatsappNumber . "?text=" . urlencode($text);

        return redirect()->away($url);
    }

    public function with()
    {
        $products = CatalogItem::query()
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))
            ->when($this->search, function($q) {
                $q->where(function ($searchQuery) {
                    $searchQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('benefits', 'like', '%' . $this->search . '%')
                        ->orWhere('health_category', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedCategory, function($q) {
                $q->where('health_category', $this->selectedCategory);
            })
            ->get();

        $categories = CatalogItem::distinct()->pluck('health_category')->filter();

        $carouselGroups = [
            [
                'key' => 'capsulas',
                'title' => 'Cápsulas',
                'icon' => '💊',
                'description' => 'Suplementos naturales en cápsulas',
                'match' => 'cáps',
            ],
            [
                'key' => 'bebidas',
                'title' => 'Bebidas',
                'icon' => '🥤',
                'description' => 'Bebidas concentradas para tu bienestar',
                'match' => 'bebid',
            ],
            [
                'key' => 'harinas',
                'title' => 'Harinas',
                'icon' => '🌾',
                'description' => 'Harinas naturales en pote y doypack',
                'match' => 'harina',
            ],
        ];

        foreach ($carouselGroups as &$group) {
            $group['products'] = $products
                ->filter(fn (CatalogItem $product): bool => str_contains(
                    mb_strtolower($product->presentation_type),
                    $group['match'],
                ))
                ->values();
        }
        unset($group);

        return [
            'products' => $products,
            'categories' => $categories,
            'carouselGroups' => $carouselGroups,
        ];
    }
};
?>

<div>
    @if (session()->has('cart_error'))
        <div style="max-width: 1200px; margin: 0 auto 12px; padding: 10px; border-radius: 6px; background: #ffebee; color: #b71c1c;">
            {{ session('cart_error') }}
        </div>
    @endif

    <div style="font-family: Arial, sans-serif; padding: 20px 0; max-width: 1200px; margin: auto; background-color: #fafafa;">
        
        <!-- BUSCADOR Y FILTROS DE CATEGORÍAS -->
        <div style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
            <input 
                id="catalog-search"
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="🔍 Buscar por medicina natural (Gastritis, Próstata, Insomnio...)" 
                style="flex: 2; min-width: 280px; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; box-sizing: border-box;"
            >

            <select wire:model.live="selectedCategory" style="flex: 1; min-width: 200px; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; background: white;">
                <option value="">Todas las Categorías de Salud</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>

            <button id="catalog-cart" type="button" wire:click="toggleCartModal" style="padding: 10px 16px; border: 0; border-radius: 8px; background: #2e7d32; color: white; font-weight: bold; cursor: pointer;">
                🛒 Carrito ({{ $this->cartCount }})
            </button>
        </div>

        <!-- CARRUSELES POR TIPO DE PRODUCTO -->
        @if($products->isEmpty())
            <div style="text-align: center; padding: 40px; background: white; border-radius: 8px; color: #888;">
                No se encontraron productos naturales con los criterios ingresados.
            </div>
        @else
            @foreach($carouselGroups as $group)
                @if($group['products']->isNotEmpty())
                    <section style="margin-bottom: 30px;">
                        <div style="display: flex; justify-content: space-between; align-items: end; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <h2 style="margin: 0; color: #1b5e20; font-size: 21px;">{{ $group['icon'] }} {{ $group['title'] }}</h2>
                                <p style="margin: 4px 0 0; color: #777; font-size: 13px;">{{ $group['description'] }}</p>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" aria-label="Ver productos anteriores" onclick="document.getElementById('catalog-carousel-{{ $group['key'] }}').scrollBy({ left: -300, behavior: 'smooth' })" style="width: 36px; height: 36px; border: 1px solid #d7e7d5; border-radius: 50%; background: white; color: #2e7d32; font-size: 20px; cursor: pointer;">‹</button>
                                <button type="button" aria-label="Ver más productos" onclick="document.getElementById('catalog-carousel-{{ $group['key'] }}').scrollBy({ left: 300, behavior: 'smooth' })" style="width: 36px; height: 36px; border: 1px solid #d7e7d5; border-radius: 50%; background: white; color: #2e7d32; font-size: 20px; cursor: pointer;">›</button>
                            </div>
                        </div>

                        <div id="catalog-carousel-{{ $group['key'] }}" style="display: flex; gap: 16px; overflow-x: auto; padding: 3px 2px 14px; scroll-snap-type: x mandatory; scrollbar-width: thin;">
                            @foreach($group['products'] as $prod)
                                <article style="flex: 0 0 260px; scroll-snap-align: start; background: white; border-radius: 10px; padding: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid #2e7d32;">
                                    <div>
                                        @if($prod->image)
                                            <img src="{{ asset('images/products/'.rawurlencode($prod->image)) }}" alt="{{ $prod->name }}" loading="lazy" style="display: block; width: 100%; height: 155px; margin-bottom: 14px; border-radius: 8px; background: #fff; object-fit: contain;">
                                        @else
                                            <div role="img" aria-label="Imagen pendiente para {{ $prod->name }}" style="display: flex; height: 155px; align-items: center; justify-content: center; margin-bottom: 14px; border-radius: 8px; background: linear-gradient(145deg, #e8efe0, #f8f8f4); color: #2e6b2f; font-size: 42px;">🌿</div>
                                        @endif
                                        <span style="background: #e8f5e9; color: #2e7d32; font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 4px;">{{ $prod->health_category }}</span>
                                        <h3 style="color: #333; margin: 10px 0 5px; font-size: 17px;">{{ $prod->name }}</h3>
                                        <p style="color: #666; font-size: 12px; margin-bottom: 12px; line-height: 1.4;">{{ Str::limit($prod->benefits, 80) }}</p>
                                        <div style="font-size: 11px; color: #888; margin-bottom: 10px;">{{ $prod->presentation_type }} · {{ $prod->weight_volume ?? 'Estándar' }}</div>
                                        <div style="font-size: 12px; font-weight: bold; color: {{ $prod->stock > 0 ? '#2e7d32' : '#c62828' }};">
                                            {{ $prod->stock > 0 ? 'Stock disponible: '.$prod->stock : 'Agotado' }}
                                        </div>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; border-top: 1px solid #f0f0f0; padding-top: 10px;">
                                        <span style="font-size: 18px; font-weight: bold; color: #1b5e20;">S/ {{ number_format($prod->price_public, 2) }}</span>
                                        <button wire:click="addToCart({{ $prod->id }})" @disabled($prod->stock < 1) style="background: {{ $prod->stock > 0 ? '#2e7d32' : '#9e9e9e' }}; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: bold; font-size: 12px; cursor: {{ $prod->stock > 0 ? 'pointer' : 'not-allowed' }};">+ Agregar</button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        @endif

        <!-- MODAL DEL CARRITO DE COMPRAS -->
        @if($showCartModal)
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;">
                <div style="background: white; border-radius: 12px; width: 100%; max-width: 500px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e8f5e9; padding-bottom: 12px; margin-bottom: 15px;">
                        <h3 style="margin: 0; color: #1b5e20; font-size: 18px;">
                            🛒 Tu Carrito de Compras
                        </h3>
                        <button wire:click="toggleCartModal" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #777;">✖</button>
                    </div>

                    @if(!$showCustomerForm)
                        <!-- VISTA 1: LISTADO DEL CARRITO -->
                        @if(count($cart) > 0)
                            <div style="margin-bottom: 20px;">
                                @foreach($cart as $item)
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                                        <div>
                                            <strong style="color: #333; font-size: 14px;">{{ $item['name'] }}</strong>
                                            <div style="font-size: 12px; color: #777;">x{{ $item['qty'] }} — S/ {{ number_format($item['price'], 2) }} c/u</div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-weight: bold; color: #2e7d32;">S/ {{ number_format($item['price'] * $item['qty'], 2) }}</span>
                                            <button wire:click="removeFromCart({{ $item['id'] }})" style="background: #ffebee; color: #c62828; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">Eliminar</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: bold; margin-bottom: 20px; background: #f1f8e9; padding: 12px; border-radius: 6px;">
                                <span>Total del Pedido:</span>
                                <span style="color: #1b5e20;">S/ {{ number_format($this->cartTotal, 2) }}</span>
                            </div>

                            <button wire:click="goToCustomerForm" style="width: 100%; background: #2e7d32; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; font-size: 14px; cursor: pointer; text-align: center;">
                                Continuar para ingresar tus datos →
                            </button>
                        @else
                            <p style="text-align: center; color: #888; padding: 30px 0;">Tu carrito está vacío. Agrega productos desde el catálogo.</p>
                        @endif

                    @else
                        <!-- VISTA 2: FORMULARIO DE REGISTRO DEL PACIENTE -->
                        <form wire:submit.prevent="processCheckoutAndGetWhatsappUrl">
                            <h4 style="color: #2e7d32; margin-top: 0; margin-bottom: 15px;">📋 Completa tus datos para finalizar el pedido:</h4>
                            @error('cart') <div style="color: #b71c1c; margin-bottom: 10px;">{{ $message }}</div> @enderror

                            <div style="margin-bottom: 10px;">
                                <label style="font-size: 12px; font-weight: bold; display: block; margin-bottom: 3px;">Nombre Completo *</label>
                                <input type="text" wire:model="customer_name" placeholder="Ej: María Condori" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                @error('customer_name') <span style="color: red; font-size: 11px;">{{ $message }}</span> @enderror
                            </div>

                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <div style="flex: 1;">
                                    <label style="font-size: 12px; font-weight: bold; display: block; margin-bottom: 3px;">DNI *</label>
                                    <input type="text" wire:model="customer_dni" placeholder="8 dígitos" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                    @error('customer_dni') <span style="color: red; font-size: 11px;">{{ $message }}</span> @enderror
                                </div>
                                <div style="flex: 1;">
                                    <label style="font-size: 12px; font-weight: bold; display: block; margin-bottom: 3px;">Edad *</label>
                                    <input type="number" wire:model="customer_age" placeholder="Ej: 48" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                    @error('customer_age') <span style="color: red; font-size: 11px;">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <div style="flex: 1;">
                                    <label style="font-size: 12px; font-weight: bold; display: block; margin-bottom: 3px;">Celular / WhatsApp *</label>
                                    <input type="text" wire:model="customer_phone" placeholder="984123456" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                    @error('customer_phone') <span style="color: red; font-size: 11px;">{{ $message }}</span> @enderror
                                </div>
                                <div style="flex: 1;">
                                    <label style="font-size: 12px; font-weight: bold; display: block; margin-bottom: 3px;">Ciudad</label>
                                    <input type="text" wire:model="customer_city" placeholder="Cusco" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                                </div>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label style="font-size: 12px; font-weight: bold; display: block; margin-bottom: 3px;">Dolencia / Padecimiento de Salud</label>
                                <input type="text" wire:model="customer_health_interest" placeholder="Ej: Gastritis, Hígado Graso" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                            </div>

                            <div style="display: flex; gap: 10px; border-top: 1px solid #eee; padding-top: 12px;">
                                <button type="button" wire:click="$set('showCustomerForm', false)" style="flex: 1; background: #e0e0e0; color: #333; border: none; padding: 10px; border-radius: 6px; font-weight: bold; cursor: pointer;">
                                    ← Volver al Carrito
                                </button>
                                <button type="submit" style="flex: 2; background: #25D366; color: white; border: none; padding: 10px; border-radius: 6px; font-weight: bold; cursor: pointer;">
                                    📲 Enviar Pedido a WhatsApp
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        @endif

    </div>
</div>
