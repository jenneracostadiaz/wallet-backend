# Sistema de Equivalencias de Monedas

## Descripción
Se ha implementado un sistema de equivalencias de monedas con PEN (Soles Peruanos) como moneda base.

## Características Implementadas

### 1. Campo de Tipo de Cambio
- Se agregó el campo `exchange_rate_to_pen` a la tabla `currencies`
- Tipo: `decimal(10, 4)` con valor por defecto de `1.0000`
- Representa cuántos PEN equivalen a 1 unidad de la moneda

### 2. Tasas de Cambio Configuradas

| Código | Moneda | Símbolo | Tasa (1 unidad = X PEN) |
|--------|--------|---------|-------------------------|
| PEN | Sol Peruano | S/ | 1.0000 |
| USD | Dólar Estadounidense | $ | 3.7800 |
| EUR | Euro | € | 4.1000 |
| GBP | Libra Esterlina | £ | 4.8500 |
| JPY | Yen Japonés | ¥ | 0.0250 |
| CAD | Dólar Canadiense | C$ | 2.7200 |
| AUD | Dólar Australiano | A$ | 2.4800 |
| CHF | Franco Suizo | CHF | 4.3500 |
| CNY | Yuan Chino | ¥ | 0.5300 |
| INR | Rupia India | ₹ | 0.0450 |

### 3. Conversión en Dashboard

La función `getCurrentTotalBalance()` en `DashboardService` ahora:

1. **Convierte todos los saldos a PEN**: Cada cuenta se multiplica por su tasa de cambio
2. **Muestra el total en PEN**: El balance total siempre se muestra en Soles
3. **Detalla por moneda**: Cada moneda muestra:
   - Balance en su moneda original
   - Balance convertido a PEN (`total_in_pen`)
   - Tasa de cambio utilizada (`exchange_rate`)

#### Ejemplo de Respuesta:

```json
{
  "total_balance": {
    "currency": {
      "code": "PEN",
      "symbol": "S/",
      "name": "Peruvian Sol"
    },
    "total": "10,000.00"
  },
  "balances_by_currency": [
    {
      "currency": {
        "code": "USD",
        "symbol": "$",
        "name": "United States Dollar"
      },
      "total": "1,000.00",
      "total_in_pen": "3,780.00",
      "exchange_rate": 3.78,
      "accounts_count": 2
    },
    {
      "currency": {
        "code": "EUR",
        "symbol": "€",
        "name": "Euro"
      },
      "total": "1,500.00",
      "total_in_pen": "6,150.00",
      "exchange_rate": 4.10,
      "accounts_count": 1
    }
  ]
}
```

## Archivos Modificados

1. **Migration**: `2025_11_01_112501_add_exchange_rate_to_currencies_table.php`
   - Agrega columna `exchange_rate_to_pen`

2. **Model**: `app/Models/Currency.php`
   - Agrega campo a `$fillable` y `$casts`

3. **Seeder**: `database/seeders/CurrencySeeder.php`
   - Actualizado para usar `updateOrCreate`
   - Incluye tasas de cambio para todas las monedas

4. **Factory**: `database/factories/CurrencyFactory.php`
   - Agrega `exchange_rate_to_pen` con valor por defecto de 1.0

5. **Service**: `app/Services/DashboardService.php`
   - Método `getCurrentTotalBalance()` actualizado para:
     - Convertir todos los saldos a PEN
     - Mostrar detalles de conversión por moneda
     - Crear moneda PEN automáticamente si no existe

6. **Tests**: `tests/Feature/DashboardServiceTest.php`
   - Actualizados para reflejar el nuevo comportamiento
   - Todos los tests pasan exitosamente

## Cómo Actualizar las Tasas de Cambio

### Opción 1: Usando el Seeder
```bash
php artisan db:seed --class=CurrencySeeder
```

### Opción 2: Directamente en la Base de Datos
```php
use App\Models\Currency;

Currency::where('code', 'USD')->update(['exchange_rate_to_pen' => 3.80]);
```

### Opción 3: Script de Actualización
Se incluye el archivo `update_exchange_rates.php` para actualizar las tasas:
```bash
php update_exchange_rates.php
```

## Notas Importantes

1. **Moneda Base**: PEN siempre debe tener una tasa de 1.0000
2. **Actualización de Tasas**: Las tasas deben actualizarse periódicamente según el mercado
3. **Precisión**: Se usan 4 decimales para mayor precisión en las conversiones
4. **Compatibilidad**: El sistema maneja correctamente cuando no hay cuentas o cuando la moneda PEN no existe (la crea automáticamente)

## Testing

Todos los tests pasan correctamente:
```bash
php artisan test --filter=DashboardServiceTest
```

Resultado: 16 tests passed (102 assertions) ✓

