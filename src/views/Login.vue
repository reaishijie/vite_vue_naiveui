<script setup lang="ts" name="Login">
import { reactive,ref } from 'vue'
import type { IUser } from '../types'
import { login } from '../utils/auth'
import { useMessage } from 'naive-ui'
import type { FormInst } from 'naive-ui'
import { LogInOutline } from '@vicons/ionicons5'
import { useUserStore } from '../store/user'
import {useRouter } from 'vue-router'
import {onMounted} from 'vue'
//检查是否已登录
onMounted(() => {
  if (userStore.logState) {
    message.success('已自动登录')
    router.push('/home')
  }
})
const router = useRouter()

const userStore = useUserStore()
const message = useMessage()

const userInfo = reactive<IUser>({
  username: '',
  password: '',
  email: ''
})

const rules = {
  username: {
    required: true,
    trigger: ['blur'],
    min: 6,
    max: 25,
    validator: (rule: any, value: string) => {
      if (!value) {return new Error('请输入账号')
    }
      if (value.length < rule.min) {return new Error('请输入账号')
    }
      if (value.length > rule.max) {return new Error('请输入账号')
    }
      return true
    }
  },
  password: {
    required: true,
    trigger: ['blur'],
    min: 6,
    max: 25,
    validator: (rule: any, value: string) => {
      if (!value) {
        return new Error('请输入密码')
      }
      if (value.length < rule.min) {
        return new Error('请确认你的密码')
      }
      if (value.length > rule.max) {
        return new Error('请确认你的密码')
      }
      return true
    }
  },
  email: {
    required: false,
    message: '请输入邮箱',
    trigger: ['blur', 'input'],
    validator: (_rule: any, value: string) => {
      if (!value) return new Error('请输入邮箱')
      const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      if (!emailRegex.test(value)) {
        return new Error('邮箱格式不正确，请输入有效的邮箱地址');
      }
      return true;

    }
  }
}

const formRef = ref<FormInst | null>(null)
const loginFunc = async () => {
  await formRef.value?.validate((errors) => {
    if (errors) {
      message.error('请正确填写账户信息')
      return
    }
  })
  await login.post("", userInfo)
  .then(response => {
    console.log('登录成功:', response)

    console.log('登录成功:', response.data)
    if (response.status === 200) {
      const userData = {
        id: response.data.data.user_id,
        username: response.data.data.username,
        password:'',
        email: response.data.data.email,
        role: response.data.data.role,
        token:response.data.data.token
      }
      userStore.setUserInfo(userData)
      userStore.setToken(response.data.data.token)
      message.success(response.data.message || '登录成功')
      console.log(userStore.logState);
      router.push('/home')
    } else {
      message.error(response.data.message || '登录失败')
    }
  })
    .catch(error => {
      message.error('登录失败：' + (error.response?.data?.message || '未知错误'))
      console.error('登录失败：:', error)
    })
}
</script>

<template>
  <div class="login-container">
    <n-card class="login-card" :bordered="false" title="用户登录">
      <n-form ref="formRef" :label-width="100" :model="userInfo" :rules="rules">
        <n-form-item label="账号" path="username">
          <n-input type="text" v-model:value="userInfo.username" placeholder="请输入账号" :round="true" :autofocus="true" />
        </n-form-item>
        <n-form-item label="密码" path="password">
          <n-input type="password" show-password-on="click" v-model:value="userInfo.password" placeholder="请输入密码" :round="true" />
        </n-form-item>
        <n-form-item>
          <n-button type="primary" block @click="loginFunc" :round="true">
            <n-icon id="LogInOutline" size="25">
              <LogInOutline />
            </n-icon>
            登 录</n-button>
        </n-form-item>
      </n-form>
    </n-card>
  </div>
</template>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
  /* 可选：设置背景色 */
}

.login-card {
  box-shadow: 0 0 8px skyblue;
  background-color: #fff;
  padding: 20px;
  width: 100%;
  max-width: 400px;
  /* 登录卡片不需要太宽 */
}

#LogInOutline {
  padding: 5px;
}

/* 响应式处理 */
@media (max-width: 480px) {
  .login-card {
    margin: 20px;
    max-width: 100%;
  }
}
</style>