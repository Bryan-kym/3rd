// Next button functionality
document.getElementById('nextBtn').addEventListener('click', async function() {
  const name = document.getElementById('signature').value.trim();
  const token = localStorage.getItem('authToken');

  try {
      // Fetch the Base64 image
      const imageResponse = await fetch('image_encode.php', {
          headers: {
              'Authorization': `Bearer ${token}`
          }
      });
      
      if (!imageResponse.ok) {
          if (imageResponse.status === 401) {
              window.location.href = 'login.php?session_expired=1';
              return;
          }
          throw new Error('Failed to fetch image');
      }
      
      const imageData = await imageResponse.text();

      // Create PDF
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF();

      // Add CONFIDENTIAL tag
      pdf.setFont("Georgia", "bold");
      pdf.setFontSize(10);
      pdf.setTextColor(0, 0, 0);
      pdf.text("PUBLIC", pdf.internal.pageSize.width - 10, 10, { align: "right" });

      // Add date
      const today = new Date();
      const formattedDate = today.toISOString().split('T')[0];

      // Add KRA logo
      const imgWidth = 90;
      const imgHeight = 23;
      const pageWidth = pdf.internal.pageSize.width;
      const xPos = (pageWidth - imgWidth) / 2;
      pdf.addImage(imageData, 'PNG', xPos, 12, imgWidth, imgHeight);

      // Add NDA content
      pdf.setFont("Arial", "normal");
      pdf.setFontSize(12);
      pdf.setTextColor(0, 0, 0);
      
      const ndaText = [
          "Terms of the Agreement",
          "This Non-Disclosure Agreement (hereinafter referred to as the \"Agreement\") is entered into on " + formattedDate,
          "by and between:",
          "1. Kenya Revenue Authority (KRA), a State Corporation in the Republic of Kenya, duly incorporated under the",
          "Kenya Revenue Authority Act (Cap. 469) of the Laws of Kenya and whose registered office is situated at Times Tower,",
          "Haile Selassie Avenue and of P.O. Box 48240 – 00100, Nairobi (hereinafter referred to as \"KRA\" which expression shall",
          "where the context so admits include its successors and assigns) of the one part; (hereinafter referred to as the",
          "(\"Disclosing Party\") and",
          "2. " + name + " (hereinafter referred to as the \"Receiving Party\") (collectively referred to as the \"Parties\")."
      ];
      
      let yPos = 50;
      ndaText.forEach(line => {
          pdf.text(line, 10, yPos);
          yPos += 10;
      });

      // Add footer
      pdf.setFont("Georgia", "bold");
      pdf.setFontSize(16);
      pdf.setTextColor(255, 0, 0);
      pdf.text("Tulipe Ushuru, Tijitegemee!", 80, 288);

      // Save PDF
      const pdfData = pdf.output('datauristring').split(',')[1];
      
      const response = await fetch('save_pdf.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify({
              pdf: pdfData,
              name: name
          })
      });
      
      const data = await response.json();
      
      if (!response.ok) {
          if (response.status === 401) {
              window.location.href = 'login.php?session_expired=1';
              return;
          }
          throw new Error(data.message || 'Failed to save PDF');
      }
      
      if (data.success) {
          localStorage.setItem('uploadedFilePath', data.filePath);
          localStorage.setItem('nda_form', data.nda_form);
          window.location.href = 'options.php';
      } else {
          throw new Error(data.message || 'Failed to save PDF');
      }
  } catch (error) {
      console.error('Error:', error);
      alert('An error occurred: ' + error.message);
  }
});