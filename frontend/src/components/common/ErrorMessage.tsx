interface ErrorMessageProps {
  message: string | null;
  onClose?: () => void;
}

export function ErrorMessage({ message, onClose }: ErrorMessageProps) {
  if (!message) return null;

  return (
    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex justify-between items-center">
      <span>{message}</span>
      {onClose && (
        <button
          onClick={onClose}
          className="text-red-700 hover:text-red-900 text-xl font-bold"
        >
          ×
        </button>
      )}
    </div>
  );
} 