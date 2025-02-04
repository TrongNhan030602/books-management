<template>
  <footer class="footer mt-2">
    <p>&copy; 2024 Đã được bảo lưu tất cả quyền.</p>
    <div class="social-icons">
      <i class="fab fa-facebook"></i>
      <i class="fab fa-twitter"></i>
      <i class="fab fa-linkedin"></i>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'FooterComponent',
};
</script>

<style scoped>
.footer {
  height: var(--footer-height);
  background: var(--background-color);
  color: var(--white-color);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
  box-shadow: 0 -2px 4px var(--box-shadow-color);
  bottom: 0;
}

.footer p {
  margin: 0;
  font-family: var(--font-family-primary);
  font-size: 0.9em;
  text-align: center;
}

.social-icons {
  display: flex;
  justify-content: center;
  margin-top: 10px;
}

.social-icons i {
  margin: 0 10px;
  font-size: 1.5em;
  color: var(--white-color);
  cursor: pointer;
  transition: transform 0.3s;
}

.social-icons i:hover {
  transform: scale(1.1);
}

/* Responsive Design */
@media (max-width: 768px) {
  .footer {
    padding: 10px;
  }

  .social-icons {
    margin-top: 5px;
  }

  .social-icons i {
    font-size: 1.2em;
    margin: 0 5px;
  }
}
</style>
