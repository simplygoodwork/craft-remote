import legacy from '@vitejs/plugin-legacy'

export default ({command}) => ({
  base: command === 'serve' ? '' : '/dist/',
  publicDir: 'non-existent-path',
  build: {
    manifest: true,
    outDir: './dist/',
    rollupOptions: {
      input: {
        app: './src/index.js',
      },
    },
  },
  server: {
    fs: {
      strict: false
    },
    host: '0.0.0.0',
    origin: 'https://localhost:3001/',
    port: 3001,
    https: true,
    strictPort: true,
  },
  plugins: [
    legacy({
      targets: ['ie >= 11'],
      additionalLegacyPolyfills: ['regenerator-runtime/runtime']
    })
  ],
})
