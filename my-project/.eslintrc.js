module.exports = {
  env: {
    browser: true,
    es2021: true,
  },
  extends: [
    'plugin:vue/vue3-essential',
    'eslint:recommended',
  ],
  parser: 'vue-eslint-parser',   // Important to parse Vue SFCs correctly
  parserOptions: {
    parser: '@babel/eslint-parser',  // Or '@typescript-eslint/parser' if using TS
    ecmaVersion: 12,
    sourceType: 'module',
  },
  globals: {
    defineProps: 'readonly',   // <-- Tell ESLint that defineProps is global
    defineEmits: 'readonly',
    defineExpose: 'readonly',
    withDefaults: 'readonly',
  },
  rules: {
    // your custom rules if any
  },
}
