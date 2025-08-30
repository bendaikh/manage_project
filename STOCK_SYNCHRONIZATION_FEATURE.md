# Stock Synchronization Feature Documentation

## Overview

This feature automatically synchronizes validated shipments with the stock table, providing comprehensive inventory management with full traceability and real-time quantity tracking.

## How It Works

### 1. Shipment Validation Process

When a shipment is marked as "Validated: Yes", the system automatically:

1. **Matches Product**: Uses the shipment's "Reference" as the primary identifier
2. **Creates/Updates Stock Record**: Either creates a new stock entry or updates existing one
3. **Sets Initial Quantities**: Establishes the baseline inventory levels
4. **Tracks Changes**: Records who made the change and when
5. **Calculates Remaining Stock**: Automatically updates remaining quantities

### 2. Stock Table Structure

The enhanced stock table includes the following fields:

#### Product Identification
- `reference` - Product reference number
- `barcode` - Product barcode (uses reference if not specified)
- `title` - Product name
- `description` - Product description

#### Inventory Quantities
- `initial_quantity` - Original quantity from shipment
- `delivered_quantity` - Quantity delivered to customers
- `damaged_quantity` - Quantity damaged/lost
- `in_progress_quantity` - Quantity being processed
- `remaining_quantity` - Calculated: Initial - (Delivered + Damaged + In Progress)

#### Pricing Information
- `purchase_price` - Cost price
- `selling_price` - Retail price

#### Status & Tracking
- `status` - 'in_stock', 'low_stock', or 'out_of_stock'
- `warehouse_location` - Storage location
- `last_updated_by` - Employee who last updated
- `last_updated_at` - Timestamp of last update

#### Additional Details
- `product_link` - Link to product page
- `photo` - Product image
- `notes` - Additional notes and history

### 3. Automatic Calculations

The system automatically:

1. **Recalculates Remaining Quantity**:
   ```
   Remaining = Initial - (Delivered + Damaged + In Progress)
   ```

2. **Updates Status Based on Remaining Quantity**:
   - `in_stock`: Remaining > 5
   - `low_stock`: Remaining ≤ 5
   - `out_of_stock`: Remaining ≤ 0

3. **Maintains Audit Trail**:
   - Records employee name and timestamp
   - Keeps history in notes field

## API Endpoints

### Stock Management

#### Get All Stocks
```
GET /stocks
```
**Query Parameters:**
- `search` - Search by title, reference, barcode, or description
- `status` - Filter by status (in_stock, low_stock, out_of_stock)
- `warehouse_location` - Filter by warehouse location
- `page` - Pagination

#### Create Stock
```
POST /stocks
```
**Required Fields:**
- `title` - Product name
- `reference` - Product reference
- `initial_quantity` - Starting quantity

**Optional Fields:**
- `barcode`, `description`, `purchase_price`, `selling_price`, `warehouse_location`, `product_link`, `photo`, `notes`

#### Update Stock
```
PUT /stocks/{id}
```
Updates all stock fields and recalculates quantities.

#### Update Quantities Only
```
PATCH /stocks/{id}/quantities
```
**Fields:**
- `delivered_quantity`
- `damaged_quantity`
- `in_progress_quantity`
- `notes`

#### Get Stock Statistics
```
GET /stocks/statistics
```
Returns:
- Total products count
- Products by status (in_stock, low_stock, out_of_stock)
- Total quantities (initial, remaining, delivered, damaged)

## Usage Examples

### 1. Validating a Shipment

When you validate a shipment through the UI:

1. Click "Validate" button on shipment
2. System automatically creates/updates stock record
3. Initial quantity is set to shipment quantity
4. Status is set to "in_stock"
5. Audit trail is created

### 2. Updating Stock Quantities

```javascript
// Update delivered quantity
fetch('/stocks/1/quantities', {
    method: 'PATCH',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        delivered_quantity: 5,
        notes: 'Delivered to customer XYZ'
    })
});
```

### 3. Getting Stock Statistics

```javascript
// Get inventory statistics
fetch('/stocks/statistics')
    .then(response => response.json())
    .then(stats => {
        console.log('Total products:', stats.total_products);
        console.log('In stock:', stats.in_stock);
        console.log('Low stock:', stats.low_stock);
        console.log('Out of stock:', stats.out_of_stock);
    });
```

## Business Logic

### Stock Creation from Shipment

When a shipment is validated:

1. **New Product**: Creates new stock record with shipment data
2. **Existing Product**: Updates existing stock record
3. **Quantity Management**: Sets initial quantity and calculates remaining
4. **Status Update**: Automatically determines stock status
5. **Audit Trail**: Records validation action with timestamp

### Quantity Updates

When quantities are updated:

1. **Validation**: Ensures quantities don't exceed initial
2. **Recalculation**: Automatically updates remaining quantity
3. **Status Check**: Updates status based on remaining quantity
4. **History**: Adds note with update details

### Status Management

- **In Stock**: Sufficient inventory (remaining > 5)
- **Low Stock**: Running low (remaining ≤ 5)
- **Out of Stock**: No inventory (remaining ≤ 0)

## Security & Permissions

### Role-Based Access

- **Sellers**: Can only manage their own stock
- **Admins/Managers**: Can manage all stock
- **Agents**: Can view and update quantities

### Data Validation

- All quantities must be non-negative
- Delivered + Damaged + In Progress cannot exceed Initial
- Reference and barcode must be unique per seller

## Error Handling

### Common Scenarios

1. **Invalid Quantities**: Returns validation error
2. **Unauthorized Access**: Returns 403 Forbidden
3. **Stock Not Found**: Returns 404 Not Found
4. **Database Errors**: Returns 500 Internal Server Error

### Error Response Format

```json
{
    "message": "Error description",
    "errors": {
        "field_name": ["Specific error message"]
    }
}
```

## Integration Points

### With Shipment System

- Automatic stock creation on shipment validation
- Stock removal on shipment invalidation
- Reference matching between shipment and stock

### With User System

- Employee tracking for all updates
- Role-based access control
- Audit trail maintenance

### With File System

- Photo upload and storage
- Automatic cleanup on deletion
- Public access for images

## Performance Considerations

### Database Optimization

- Indexed fields: reference, barcode, status, seller_id
- Efficient queries with proper joins
- Pagination for large datasets

### Caching Strategy

- Stock statistics caching
- Frequently accessed stock data
- Cache invalidation on updates

## Future Enhancements

### Planned Features

1. **Barcode Scanning**: Mobile app integration
2. **Warehouse Management**: Multi-location support
3. **Automated Alerts**: Low stock notifications
4. **Reporting**: Advanced analytics and reports
5. **API Webhooks**: Real-time notifications

### Scalability

- Support for multiple warehouses
- Advanced inventory forecasting
- Integration with external systems
- Mobile application support

## Troubleshooting

### Common Issues

1. **Stock Not Created**: Check shipment validation status
2. **Quantities Not Updating**: Verify permissions and data format
3. **Status Not Changing**: Check remaining quantity calculation
4. **Photo Not Displaying**: Verify file storage configuration

### Debug Information

Enable logging to track:
- Stock creation/update events
- Quantity calculation details
- Permission checks
- Error conditions

## Support

For technical support or questions about this feature, please refer to:
- API documentation
- Database schema
- Controller source code
- Model relationships
