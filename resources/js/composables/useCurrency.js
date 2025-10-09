// Currency composable for formatting currency values
export function useCurrency() {
  const getCurrency = () => {
    return window.Laravel?.settings?.currency || 'FCFA'
  }

  const formatCurrency = (amount) => {
    if (amount === null || amount === undefined) return 'N/A'
    const currency = getCurrency()
    const value = parseFloat(amount)
    
    if (isNaN(value)) return 'N/A'
    
    // Format number with thousand separators
    const formatted = value.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
    
    return `${formatted} ${currency}`
  }

  return {
    getCurrency,
    formatCurrency
  }
}

