# Marketplace (COD Products) View Modes Feature

## Overview
Updated the **Marketplace** section (COD Products for sellers) to support multiple view modes with enhanced user experience.

## View Modes

### 1. **Card View** (Default) ✨
A modern, visually-appealing card layout featuring:
- **Large square product images** with aspect-ratio preservation
- **Overlay badges** for category (blue) and status (color-coded)
- **Hover effects** with smooth zoom animation and eye icon
- **Comprehensive product details**:
  - Product name (with line-clamp for long names)
  - SKU with icon
  - Stock information per warehouse (shows first 2, with "+X more" indicator)
  - Total stock quantity with low stock warning
  - Selling price (prominent, large font)
- **Action button**:
  - View Details (blue) - View full product information
- **Responsive grid**: 1-4 columns based on screen size

#### Visual Features:
- Rounded corners with shadow that enhances on hover
- Gradient overlay on image hover
- Color-coded status badges (Green: In Stock, Yellow: Low Stock, Red: Out of Stock, Gray: Discontinued)
- Smooth transitions and animations
- Low stock warning badges (⚠️) for products with ≤10 units

### 2. **Table View**
Traditional tabular layout with:
- Columns: Product, SKU, Category, Price, Stock, Status, Actions
- Small product thumbnails (10x10) in the product column
- Compact information display
- Hover effects on rows
- Color-coded status badges
- Warehouse stock breakdown
- Ideal for data-heavy viewing and comparison

## Technical Details

### Component: `MarketplaceList.vue`
**Location**: `resources/js/components/MarketplaceList.vue`

### View Persistence
- The selected view mode is saved to `localStorage` as `marketplaceViewType`
- Default view: `card`
- View preference persists across sessions

### View Selector
Located between the filters and product display with two buttons:
- **Cards** - Shows card icon with label
- **Table** - Shows table icon with label
- Active view is highlighted with blue accent and white background

## Responsive Behavior

### Card View Grid Breakpoints:
- Mobile (default): 1 column
- Small (sm): 2 columns
- Large (lg): 3 columns
- Extra Large (xl): 4 columns

### Table View:
- Horizontal scrolling on smaller screens
- Fixed header for easy navigation
- White background with shadow

## Key Features

1. **Enhanced Visual Hierarchy**: Card view puts product images front and center
2. **Better Information Density**: Card view balances aesthetics with information
3. **Improved User Experience**: Smooth transitions, hover effects, and intuitive layout
4. **Warehouse Support**: Displays stock per warehouse with clear visual indicators
5. **Low Stock Warnings**: Prominent warnings for products with 10 or fewer units
6. **Status Visibility**: Color-coded status badges for quick product status identification

## Color Scheme

- **Primary**: Blue (#2563EB) - Brand color for buttons and accents
- **Success**: Green - In Stock status
- **Warning**: Yellow - Low Stock status  
- **Danger**: Red - Out of Stock status
- **Neutral**: Gray - Discontinued status
- **Info**: Blue - Warehouse indicators and category badges

## Stock Status Logic

- **Low Stock Warning**: Displayed when `stock_quantity <= 10`
- Shown in both Card and Table views
- Visual indicator: Yellow badge with warning emoji (⚠️)

## Usage for Sellers

1. Log in as a seller account
2. Navigate to **COD Products** in the sidebar
3. Use the view selector buttons at the top to switch between Card View and Table View
4. Your preference will be remembered for future visits
5. Click on any product card or "View" button to see full product details

## Files Modified

1. `resources/js/components/MarketplaceList.vue` - Main marketplace component with view modes
2. Built assets in `public/build/` - Compiled JavaScript and CSS

## Differences from ProductList

While similar in design, the Marketplace view has key differences:
- **Target Audience**: Sellers only (sellers viewing their assigned COD products)
- **Functionality**: View-only (no edit/delete actions)
- **Data Source**: Products assigned to the seller via `/marketplace` endpoint
- **Statistics**: Shows seller-specific stats (total products, in stock, low stock, total value)
- **Permissions**: Restricted to users with 'seller' role
- **Color Scheme**: Blue-themed (vs violet in ProductList)

## API Endpoints

- **GET /marketplace**: Fetch seller's assigned products (with filters, sorting, pagination)
- **GET /marketplace/stats**: Fetch seller-specific statistics

## Future Enhancements (Optional)

- Add "Add to Order" quick action from Card view
- Add product comparison feature
- Add export functionality for seller reports
- Add product availability notifications
- Add product performance metrics (sales history)
- Add quick contact admin for out-of-stock products

## Notes

This feature is part of the COD (Cash on Delivery) product management system where sellers can view products assigned to them by the company. Sellers can only view products, not modify them, ensuring inventory control remains centralized.

