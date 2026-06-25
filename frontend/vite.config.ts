import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

function injectFavicon(): import('vite').Plugin {
  const faviconPath = path.resolve(__dirname, '../shared/favicon/head.html')
  const faviconHtml = fs.readFileSync(faviconPath, 'utf-8').trim()

  return {
    name: 'inject-favicon',
    transformIndexHtml(html) {
      return html.replace('<!-- favicon -->', faviconHtml)
    },
  }
}

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), injectFavicon()],
})
