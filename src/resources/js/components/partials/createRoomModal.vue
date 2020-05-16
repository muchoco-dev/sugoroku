<template>
    <transition name="modal">
        <div class="modal-mask">
          <div class="modal-wrapper">
            <div class="modal-container">

              <div class="modal-body">
                <slot name="body">
                <p>部屋名を入力してください</p>
                <div>名前<input v-model="message"></div>
                <button @click="doSend">送信</button>
                </slot>
              </div>

              <div class="modal-footer">
                <slot name="footer">
                  <button class="modal-default-button" @click="$emit('close')">
                    X
                  </button>
                </slot>
              </div>
            </div>
          </div>
        </div>
      </transition>
</template>
<script>
export default {
  data() {
    return {
      name: ''
    }
  },
  methods: {
    doSend() {
        alert('送ったことにするーあとでつくるー');
    }
  }
}
</script>
