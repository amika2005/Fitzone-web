function loginsuccess() {
    alert("Login Successfull");
    window.location.href = "index.html";
}

document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for scroll animations
    const animateOnScroll = function() {
      const elements = document.querySelectorAll('.animate-fadeInUp, .animate-fadeIn, .animate-scaleUp, .animate-slideInLeft, .animate-slideInRight');
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add(entry.target.dataset.animation);
            observer.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.1
      });
  
      elements.forEach(element => {
        // Store the original animation class in a data attribute
        const animationClass = Array.from(element.classList).find(cls => cls.startsWith('animate-'));
        if (animationClass) {
          element.dataset.animation = animationClass;
          element.classList.remove(animationClass);
          observer.observe(element);
        }
      });
    };
  
    animateOnScroll();
  });