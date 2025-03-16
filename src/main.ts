import { createApp } from 'vue'
import App from './App.vue'
import { createPinia } from 'pinia'
import router  from './router'
import naive from 'naive-ui'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
pinia.use(piniaPluginPersistedstate)
app.use(router)
app.use(naive)
app.mount('#app')
