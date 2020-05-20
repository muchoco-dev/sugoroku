<template>
    <transition name="modal">
        <div class="modal-mask">
          <div class="modal-wrapper">
            <div class="modal-container">

              <div class="modal-body">
                <slot name="body">
                <p>部屋名を入力してください</p>
                <div>名前<input v-model="name"></div>
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
  props: {
      token: String
  },
  data() {
    return {
      name: ''
    }
  },
  methods: {
    doSend() {
      axios.defaults.headers.common['Authorization'] = "Bearer " + this.token;
      axios.post(
        '/api/room/create',
        {
          headers: {
            "Content-Type": "application/json"
          },
          'name': this.name
      }).then(function(response) {
        if (response.data.status === 'success') {
            console.log('部屋を作成できました。');
        } else {
            alert(response.data.message);
        }
      }).catch(function(error) {
        console.log(error);
      });

      this.$emit('close');
    }
  }
}
</script>
