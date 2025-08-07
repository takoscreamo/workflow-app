interface LoadingSpinnerProps {
  message?: string;
  size?: 'sm' | 'md' | 'lg';
  showMessage?: boolean;
  className?: string;
}

export function LoadingSpinner({ 
  message = "読み込み中...", 
  size = 'md',
  showMessage = true,
  className = ""
}: LoadingSpinnerProps) {
  const sizeClasses = {
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8'
  };

  const textSizes = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base'
  };

  return (
    <div className={`flex items-center justify-center ${className}`}>
      <div className="flex items-center space-x-2">
        <div className={`animate-spin rounded-full border-2 border-gray-300 border-t-blue-600 ${sizeClasses[size]}`}></div>
        {showMessage && (
          <span className={`text-gray-600 ${textSizes[size]}`}>{message}</span>
        )}
      </div>
    </div>
  );
} 