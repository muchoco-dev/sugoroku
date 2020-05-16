<template>
  <transition name="modal" appear>
    <div class="modal modal-overlay" @click.self="$emit('close')">
      <div class="modal-window">
        <div class="modal-content">
            <p>部屋名を入力してください</p>
            <div>名前<input v-model="message"></div>
            <button @click="doSend">送信</button>
        </div>
        <footer class="modal-footer">
            <button @click="$emit('close')">Close</button>
        </footer>
      </div>
    </div>
  </transition>
</template>

<style lang="stylus" scoped>
.modal {
  &.modal-overlay {
    display: flex;
    align-items: center;
    justify-content: center;
    position: fixed;
    z-index: 30;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
  }

  &-window {
    background: #fff;
    border-radius: 4px;
    overflow: hidden;
  }

  &-content {
    padding: 10px 20px;
  }

  &-footer {
    background: #ccc;
    padding: 10px;
    text-align: right;
  }
}

// オーバーレイのトランジション
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.4s;

  // オーバーレイに包含されているモーダルウィンドウのトランジション
  .modal-window {
    transition: opacity 0.4s, transform 0.4s;
  }
}

// ディレイを付けるとモーダルウィンドウが消えた後にオーバーレイが消える
.modal-leave-active {
  transition: opacity 0.6s ease 0.4s;
}

.modal-enter, .modal-leave-to {
  opacity: 0;

  .modal-window {
    opacity: 0;
    transform: translateY(-20px);
  }
}
</style>
<script>
    export default {
        showCreateForm() {
            return {
            showCreateForm: false,
            }
        },
    }
</script>