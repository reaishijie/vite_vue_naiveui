<script setup lang="ts" name="Register">
import { reactive,ref } from 'vue'
import { register } from '../utils/auth'
import { type IUser } from '../types/index'
import { LogInOutline, Sync, CheckmarkOutline } from '@vicons/ionicons5'
import { useMessage, NIcon,type FormInst, NCard, NForm, NInput, NButton, NFormItem } from 'naive-ui'
import { useRouter } from 'vue-router'
const router = useRouter()
const message = useMessage()
import {onMounted} from 'vue'
import { useUserStore } from '../store/user'

const userStore = useUserStore()
//检查是否已登录
onMounted(() => {
  if (userStore.logState) {
    message.success('已自动登录')
    router.push('/home')
  }
})
//初始化响应式表单信息
const userInfo = reactive<IUser>({
  username: '',
  password: '',
  email: ''
})
//定义验证规则
const rules = {
  username: {
    required: true,
    trigger: ['blur', 'input'],
    min: 6,
    max: 25,
    validator: (rule: any, value: string) => {
      if (!value) {return new Error('请输入账号')}
      if (value.length < rule.min) {return new Error('账号的长度至少是'+rule.min+'位，当前为' + value.length + '位')}
      if (value.length > rule.max) {return new Error('账号的长度最多是'+rule.max+'位，当前为' + value.length + '位')}
      return true
    }
  },
  password: {
    required: true,
    trigger: ['blur', 'input'],
    min: 6,
    max: 25,
    validator: (rule: any, value: string) => {
      if (!value) {
        return new Error('请输入密码')
      } 
      if (value.length < rule.min) {
        return new Error('密码的长度至少是'+rule.min+'位，当前为' + value.length + '位')
      }
      if (value.length > rule.max) {
        return new Error('密码的长度最多是'+rule.max+'位，当前为' + value.length + '位')
      }
      return true
    }
  },
  email: {
    required: true,
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
const formRef = ref<FormInst | null >(null)
//注册按钮功能
const registerFunc = () => {
  formRef.value?.validate((errors) => {
    if (errors) {
      message.error('请正确填写所有信息')
      return
    }
    register.post('', userInfo)
      .then(()=> {
        message.success('注册成功')
        router.push('/login')
      })
      .catch(error => {
        message.error('注册失败：' + (error.response?.data?.message || '未知错误'))
      })
  })
}
//重置信息按钮
const reset = () => {
  userInfo.username = '',
    userInfo.password = '',
    userInfo.email = ''
}
</script>

<template>
  <div class="register-container">
    <n-card class="register-card" :bordered="false" title="用户注册">
      <n-form ref="formRef" :label-width="100" :model="userInfo" :rules="rules">
        <n-form-item label="账号：" path="username" >
          <n-input v-model:value="userInfo.username" placeholder="qq号/手机号"  :autofocus="true" :round="true"  />
        </n-form-item>
        <n-form-item label="密码：" path="password">
          <n-input v-model:value="userInfo.password" type="password" show-password-on="click" placeholder="最好不是123456" :round="true" />
        </n-form-item>
        <n-form-item label="邮箱：" path="email">
          <n-input v-model:value="userInfo.email" type="text" placeholder="可以是你qq号@qq.com"  :round="true"/>
        </n-form-item>
      </n-form>
      <n-button type="primary" @click="registerFunc" :round="true" id="registerButton" >
        <n-icon>
          <CheckmarkOutline />
        </n-icon>
        注册</n-button>
      <n-button @click="reset" :round="true" style="margin-left: 10px;">
        <template #icon>
          <n-icon>
            <Sync />
          </n-icon>
        </template>
        重置</n-button>
      <p>Already have an account ?
      <router-link to="login"> <n-icon size="25">
            <LogInOutline />
          </n-icon>
          click here to Login</router-link>
      </p>
      <template>
        <n-popover trigger="hover">
          <template #trigger>
            <n-button>悬浮</n-button>
          </template>
          <span> click here to Login</span>
        </n-popover>
      </template>
    </n-card>
  </div>
</template>

<style scoped>
.register-container {
  display: flex;
  background-color: #f5f5f5;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

.register-card {
  background-color: #fff;
  max-width: 400px;
  width: 100%;
  padding: 20px;
  box-shadow: 0 0 8px skyblue;
}
</style>
