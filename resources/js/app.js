/// resources/js/app.js

import { createApp }   from 'vue'
import { createPinia } from 'pinia'
import App    from './App.vue'          // resources/js/App.vue
import router from './router'           // resources/js/router/index.js
import { i18n } from './i18n'          // resources/js/i18n/index.js

const app   = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(i18n)

app.mount('#app')