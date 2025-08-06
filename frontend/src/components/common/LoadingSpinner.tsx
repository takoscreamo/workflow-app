interface LoadingSpinnerProps {
  message?: string;
}

export function LoadingSpinner({ message = "読み込み中..." }: LoadingSpinnerProps) {
  return (
    <div className="min-h-screen flex items-center justify-center">
      <div className="text-xl">{message}</div>
    </div>
  );
} 