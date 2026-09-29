import { defineConfig } from 'vite'
import type { Plugin } from 'vite'
import react from '@vitejs/plugin-react'
import fs from 'fs'
import path from 'path'

// The PrestaShop Addons validator requires stylesheets under /views/css, but Vite emits
// them next to the JS bundle, so move them out of the bundle into that folder.
const stylesheetOutputDir = path.resolve(__dirname, '../../../css/admin')

function emitStylesheetsToViewsCss(): Plugin {
  return {
    name: 'saferpay-stylesheets-to-views-css',
    enforce: 'post',
    generateBundle(_options, bundle) {
      for (const [fileName, output] of Object.entries(bundle)) {
        if (output.type !== 'asset' || !fileName.endsWith('.css')) {
          continue
        }

        fs.mkdirSync(stylesheetOutputDir, { recursive: true })
        fs.writeFileSync(path.join(stylesheetOutputDir, path.basename(fileName)), output.source)

        delete bundle[fileName]
      }
    },
  }
}

export default defineConfig({
  plugins: [react(), emitStylesheetsToViewsCss()],
  base: './',
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  build: {
    outDir: '../dist',
    emptyOutDir: true,
    rollupOptions: {
      output: {
        entryFileNames: 'saferpay-settings.js',
        assetFileNames: 'saferpay-settings.[ext]',
      },
    },
  },
})
