import js from '@eslint/js'
import vuePlugin from 'eslint-plugin-vue'

export default [
  // Globale Ignorierliste (build-Output etc.)
  {
    ignores: [
      'public/build/**',
      'vendor/**',
      'node_modules/**',
      // ggf. weitere:
      '.ddev/**',
      '.idea/**',
    ],
  },

  // Basis-JS-Regeln
  {
    files: ['**/*.{js,mjs,cjs}'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
    },
    rules: {
      ...js.configs.recommended.rules,
    },
  },

  // Vue SFC (.vue) Support
  {
    files: ['**/*.vue'],
    languageOptions: {
      parser: 'vue-eslint-parser',
      parserOptions: {
        ecmaVersion: 2022,
        sourceType: 'module',
      },
    },
    plugins: {
      vue: vuePlugin,
    },
    rules: {
      ...vuePlugin.configs['vue3-essential'].rules, // schlank & praxisnah
      // Optionale Schärfungen:
      'vue/no-mutating-props': 'off',
      'vue/multi-word-component-names': 'off',
    },
  },
]
