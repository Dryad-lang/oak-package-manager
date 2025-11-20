/** @type {import('next').NextConfig} */
const nextConfig = {
  output: 'standalone',
  experimental: {
    serverComponentsExternalPackages: []
  },
  env: {
    NEXT_PUBLIC_API_URL: process.env.NEXT_PUBLIC_API_URL || 'http://oak.dryadlang.org:4000',
    NEXT_PUBLIC_GITEA_URL: process.env.NEXT_PUBLIC_GITEA_URL || 'http://oak.dryadlang.org:3000'
  },
  images: {
    remotePatterns: [
      {
        protocol: 'http',
        hostname: 'oak.dryadlang.org'
      },
      {
        protocol: 'https',
        hostname: '**'
      }
    ]
  },
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: `${process.env.NEXT_PUBLIC_API_URL}/api/:path*`
      }
    ];
  }
};

module.exports = nextConfig;