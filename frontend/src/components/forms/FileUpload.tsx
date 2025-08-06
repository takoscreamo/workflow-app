interface FileUploadProps {
  label: string;
  accept?: string;
  onChange: (file: File | null) => void;
  required?: boolean;
  className?: string;
}

export function FileUpload({ 
  label, 
  accept = "*", 
  onChange, 
  required = false,
  className = ''
}: FileUploadProps) {
  return (
    <div className={className}>
      <label className="block text-sm font-medium text-gray-700 mb-1">
        {label}
        {required && <span className="text-red-500 ml-1">*</span>}
      </label>
      <input
        type="file"
        accept={accept}
        onChange={(e) => onChange(e.target.files?.[0] || null)}
        required={required}
        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
    </div>
  );
} 