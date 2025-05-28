<!-- footer.php -->
<footer>
    <div class="footer-container">
        <div class="footer-box-info">
            <h2>Vietējais brīvprātīgās centrs</h2>
            <p>Vietējo sadarbība un kopā strādāšana, lai pastiprinātu attiecības</p>
            <p class="copy">&copy; <?= date("Y") ?> Vietējais brīvprātīgās centrs</p>
        </div>

        <div class="footer-box-follow">
            <h3>Seko mums</h3>
            <div class="footer-icons">
                <i class="fa-brands fa-square-facebook"></i>
                <i class="fa-brands fa-square-x-twitter"></i>
                <i class="fa-brands fa-square-instagram"></i>
            </div>
            <h3>Zvaniet mums</h3>
            <p>+371 11 111 111</p>
        </div>

        <div class="footer-box-comp">
            <h3>Informācija</h3>
            <p>Par mums</p>
            <p>FAQs</p>
            <p>Sazināties</p>
        </div>
    </div>
    <div class="footer-bottom">
        <a href="#">Privātuma politika</a>
        <a href="#">Lietošanas noteikumi</a>
    </div>
</footer>
<style>
    
/* Base footer styles */
footer {
  background-color: #222; /* Dark background for professionalism */
  color: #eee;
  padding: 30px 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 16px;
  line-height: 1.5;
}

/* Container for the three boxes */
.footer-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  max-width: 1200px;
  margin: 0 auto;
}

/* Each box styling */
.footer-box-info,
.footer-box-follow,
.footer-box-comp {
  flex: 1 1 30%; /* Grow and shrink, basis 30% */
  min-width: 200px; /* Prevent too narrow */
  margin: 10px;
}

/* Headings */
.footer-box-info h2,
.footer-box-follow h3,
.footer-box-comp h3 {
  margin-bottom: 15px;
  color: #fff;
  font-weight: 700;
}

/* Paragraphs */
.footer-box-info p,
.footer-box-follow p,
.footer-box-comp p {
  margin: 8px 0;
  color: #ccc;
}

/* Copy right text */
.copy {
  margin-top: 20px;
  font-size: 14px;
  color: #888;
}

/* Social icons container */
.footer-icons {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

/* Social icons styling */
.footer-icons i {
  font-size: 24px;
  color: #ccc;
  cursor: pointer;
  transition: color 0.3s ease;
}

.footer-icons i:hover {
  color: #1da1f2; /* Example hover color */
}

/* Footer bottom links */
.footer-bottom {
  border-top: 1px solid #444;
  margin-top: 30px;
  padding-top: 15px;
  text-align: center;
  font-size: 14px;
}

.footer-bottom a {
  color: #ccc;
  margin: 0 15px;
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer-bottom a:hover {
  color: #fff;
}

/* Responsive adjustments */

/* Medium screens (below 900px) */
@media (max-width: 900px) {
  .footer-box-info,
  .footer-box-follow,
  .footer-box-comp {
    flex: 1 1 45%; /* Two columns */
  }
}

/* Small screens (below 600px) */
@media (max-width: 600px) {
  footer {
    font-size: 14px;
    padding: 25px 15px;
  }
  .footer-box-info,
  .footer-box-follow,
  .footer-box-comp {
    flex: 1 1 48%; /* Two columns with some margin */
    margin: 8px 4px;
  }
  .footer-icons i {
    font-size: 20px;
  }
}

/* Extra small screens (down to 360px) */
@media (max-width: 360px) {
  footer {
    font-size: 13px;
    padding: 20px 10px;
  }
  .footer-container {
    justify-content: center;
  }
  .footer-box-info,
  .footer-box-follow,
  .footer-box-comp {
    flex: 1 1 100px; /* Allow wrapping but keep side by side if possible */
    margin: 6px 5px;
  }
  .footer-icons {
    gap: 10px;
  }
  .footer-icons i {
    font-size: 18px;
  }
  .footer-bottom a {
    margin: 0 10px;
  }
}
</style>