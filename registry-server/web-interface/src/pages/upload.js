import React, { useState, useCallback } from 'react';
import { useRouter } from 'next/router';
import { useDropzone } from 'react-dropzone';
import { Upload, Package, CheckCircle, AlertCircle, Loader } from 'lucide-react';
import { toast } from 'react-toastify';
import Layout from '../components/Layout';
import { uploadPackage } from '../utils/api';

export default function UploadPage() {
  const router = useRouter();
  const [uploading, setUploading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState(0);
  const [dragActive, setDragActive] = useState(false);

  const onDrop = useCallback(async (acceptedFiles) => {
    if (acceptedFiles.length === 0) return;
    
    const file = acceptedFiles[0];
    
    // Validate file type
    if (!file.name.endsWith('.tar.gz') && !file.name.endsWith('.tgz')) {
      toast.error('Please upload a .tar.gz or .tgz file');
      return;
    }

    // Validate file size (max 100MB)
    if (file.size > 100 * 1024 * 1024) {
      toast.error('File size must be less than 100MB');
      return;
    }

    setUploading(true);
    setUploadProgress(0);

    try {
      const result = await uploadPackage(file, (progress) => {
        setUploadProgress(progress);
      });

      toast.success(`Package ${result.package.name}@${result.package.version} uploaded successfully!`);
      router.push(`/packages/${result.package.name}`);
    } catch (error) {
      console.error('Upload error:', error);
      toast.error(error.response?.data?.message || 'Failed to upload package');
    } finally {
      setUploading(false);
      setUploadProgress(0);
    }
  }, [router]);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept: {
      'application/gzip': ['.tar.gz', '.tgz']
    },
    maxFiles: 1,
    onDragEnter: () => setDragActive(true),
    onDragLeave: () => setDragActive(false)
  });

  return (
    <Layout title="Upload Package">
      <div className="max-w-4xl mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Upload Package</h1>
          <p className="text-gray-600">
            Upload your Dryad package to the registry. Make sure your package includes a valid oaklibs.json file.
          </p>
        </div>

        {/* Upload Area */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
          <div
            {...getRootProps()}
            className={`border-2 border-dashed rounded-lg p-12 text-center transition-colors cursor-pointer ${
              isDragActive || dragActive
                ? 'border-blue-400 bg-blue-50'
                : 'border-gray-300 hover:border-gray-400'
            } ${uploading ? 'pointer-events-none opacity-50' : ''}`}
          >
            <input {...getInputProps()} disabled={uploading} />
            
            {uploading ? (
              <div className="space-y-4">
                <Loader className="w-12 h-12 text-blue-500 mx-auto animate-spin" />
                <div>
                  <p className="text-lg font-medium text-gray-900 mb-2">
                    Uploading package...
                  </p>
                  <div className="w-full bg-gray-200 rounded-full h-2 mb-2">
                    <div
                      className="bg-blue-500 h-2 rounded-full transition-all duration-300"
                      style={{ width: `${uploadProgress}%` }}
                    />
                  </div>
                  <p className="text-sm text-gray-600">{uploadProgress.toFixed(1)}% complete</p>
                </div>
              </div>
            ) : (
              <div className="space-y-4">
                <Upload className="w-12 h-12 text-gray-400 mx-auto" />
                <div>
                  <p className="text-lg font-medium text-gray-900">
                    {isDragActive ? 'Drop your package here' : 'Upload your package'}
                  </p>
                  <p className="text-gray-600">
                    Drag and drop a .tar.gz file here, or click to select
                  </p>
                </div>
                <div className="text-sm text-gray-500">
                  Maximum file size: 100MB
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Package Requirements */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Package Requirements</h2>
          <div className="space-y-3">
            <div className="flex items-start space-x-3">
              <CheckCircle className="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" />
              <div>
                <p className="font-medium text-gray-900">Valid oaklibs.json</p>
                <p className="text-sm text-gray-600">
                  Your package must include a properly formatted oaklibs.json file with name, version, and description.
                </p>
              </div>
            </div>
            
            <div className="flex items-start space-x-3">
              <CheckCircle className="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" />
              <div>
                <p className="font-medium text-gray-900">Semantic Versioning</p>
                <p className="text-sm text-gray-600">
                  Version must follow semantic versioning (e.g., 1.0.0, 2.1.3-beta.1).
                </p>
              </div>
            </div>
            
            <div className="flex items-start space-x-3">
              <CheckCircle className="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" />
              <div>
                <p className="font-medium text-gray-900">Package Structure</p>
                <p className="text-sm text-gray-600">
                  Follow the standard Dryad package structure with src/ for main code and lib/ for modules.
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Example Structure */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Example Package Structure</h2>
          <pre className="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 overflow-x-auto">
{`my-package/
├── oaklibs.json          # Package configuration
├── README.md             # Documentation
├── src/                  # Main source code
│   └── main.dryad
├── lib/                  # Exportable modules
│   ├── utils.dryad
│   └── helpers.dryad
└── tests/                # Test files (optional)
    └── test_main.dryad`}
          </pre>
        </div>

        {/* Example oaklibs.json */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Example oaklibs.json</h2>
          <pre className="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 overflow-x-auto">
{`{
  "name": "my-awesome-package",
  "version": "1.0.0",
  "description": "An awesome Dryad package",
  "author": "Your Name <your.email@example.com>",
  "license": "MIT",
  "keywords": ["dryad", "utility", "helper"],
  "homepage": "https://github.com/yourusername/my-awesome-package",
  "repository": {
    "type": "git",
    "url": "https://github.com/yourusername/my-awesome-package.git"
  },
  "dependencies": {
    "dryad-stdlib": "^0.1.0"
  },
  "dev_dependencies": {
    "dryad-test": "^1.0.0"
  }
}`}
          </pre>
        </div>
      </div>
    </Layout>
  );
}