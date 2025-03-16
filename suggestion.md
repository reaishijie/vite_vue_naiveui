有几种方式可以存储和管理 token，我推荐使用 localStorage 或 Pinia 来实现。下面是具体实现方案：

1. **创建 Token 管理工具**

```typescript
// src/utils/token.ts
export const TokenManager = {
  // 设置token
  setToken(token: string) {
    localStorage.setItem('token', token)
  },
  
  // 获取token
  getToken(): string | null {
    return localStorage.getItem('token')
  },
  
  // 删除token
  removeToken() {
    localStorage.removeItem('token')
  },
  
  // 检查是否有token
  hasToken(): boolean {
    return !!this.getToken()
  }
}
```

2. **创建 Pinia Store 管理用户状态**

```typescript
// src/stores/user.ts
import { defineStore } from 'pinia'
import { TokenManager } from '../utils/token'

interface UserState {
  token: string | null
  userInfo: {
    user_id: number | null
    username: string
    email: string
    role: string
  }
}

export const useUserStore = defineStore('user', {
  state: (): UserState => ({
    token: TokenManager.getToken(),
    userInfo: {
      user_id: null,
      username: '',
      email: '',
      role: ''
    }
  }),
  
  actions: {
    setToken(token: string) {
      this.token = token
      TokenManager.setToken(token)
    },
  
    setUserInfo(info: any) {
      this.userInfo = info
    },
  
    logout() {
      this.token = null
      this.userInfo = {
        user_id: null,
        username: '',
        email: '',
        role: ''
      }
      TokenManager.removeToken()
    }
  },
  
  getters: {
    isLoggedIn(): boolean {
      return !!this.token
    }
  }
})
```

3. **修改登录组件**

```typescript
<script setup lang="ts" name="Login">
import { reactive } from 'vue'
import type { IUser } from '../types'
import { login } from '../utils/auth'
import { useMessage } from 'naive-ui'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'

const router = useRouter()
const message = useMessage()
const userStore = useUserStore()

const userInfo = reactive<IUser>({
  username: '',
  password: '',
  email: ''
})

const loginFunc = async () => {
  try {
    const response = await login.post("", userInfo)
    const { token, ...userData } = response.data.data
  
    // 存储token和用户信息
    userStore.setToken(token)
    userStore.setUserInfo(userData)
  
    message.success('登录成功')
  
    // 跳转到首页
    router.push('/home')
  } catch (error: any) {
    message.error('登录失败：' + (error.response?.data?.message || '未知错误'))
    console.error('登录失败：:', error)
  }
}
</script>
```

4. **添加路由守卫**

```typescript
// src/router/index.ts
import { createRouter, createWebHistory } from 'vue-router'
import { TokenManager } from '../utils/token'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // 你的路由配置
  ]
})

// 需要登录才能访问的路由
const authRoutes = ['/home', '/profile']

router.beforeEach((to, from, next) => {
  if (authRoutes.includes(to.path)) {
    if (!TokenManager.hasToken()) {
      next('/login')
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
```

5. **配置 Axios 拦截器自动添加 token**

```typescript
// src/utils/request.ts
import axios from 'axios'
import { TokenManager } from './token'

const request = axios.create({
  baseURL: 'your-api-base-url'
})

// 请求拦截器
request.interceptors.request.use(
  config => {
    const token = TokenManager.getToken()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// 响应拦截器
request.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // token过期或无效
      TokenManager.removeToken()
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default request
```

6. **使用示例**

```typescript
<!-- 在其他组件中使用 -->
<script setup lang="ts">
import { useUserStore } from '../stores/user'

const userStore = useUserStore()

// 检查是否登录
const checkAuth = () => {
  if (!userStore.isLoggedIn) {
    // 处理未登录状态
  }
}

// 获取用户信息
const getUserInfo = () => {
  const userInfo = userStore.userInfo
  console.log(userInfo)
}

// 退出登录
const handleLogout = () => {
  userStore.logout()
  router.push('/login')
}
</script>
```

这样的设计有以下优点：

1. 统一管理 token
2. 支持持久化存储
3. 方便在多个组件中使用
4. 提供了完整的类型支持
5. 包含了必要的安全处理
6. 易于扩展和维护

注意事项：

1. 记得在用户退出时清除 token
2. 考虑 token 过期的处理
3. 敏感操作时可能需要重新验证
4. 建议使用 HTTPS 传输
5. 考虑添加 token 刷新机制
