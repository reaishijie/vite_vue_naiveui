import { createApp } from 'vue'
import App from './App.vue'
import { createPinia } from 'pinia'
import router  from './router'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
pinia.use(piniaPluginPersistedstate)
app.use(router)
app.mount('#app')
