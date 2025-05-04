function showContent(questId, event) {
    const rightPane = document.getElementById("right-pane");
    rightPane.innerHTML = "";
  
    const filteredAnswers = allAnswers.filter(answer => answer.quest_id == questId);
  
    // Always apply the active class to the clicked button
    const allButtons = document.querySelectorAll(".button"); // select all buttons
    allButtons.forEach((btn) => btn.classList.remove("active"));
    const button = event.target;
    button.classList.add("active");

    if (filteredAnswers.length === 0) {
      rightPane.innerHTML = "<h3>No answers for this quest</h3>";
      return;
    }
  
    filteredAnswers.forEach((answer, index) => {
      const card = document.createElement("div");
      card.className = "card";
  
      const cardImage = document.createElement("div");
      cardImage.className = "card-image";
  
      const cardInnerImage = document.createElement("img");
      cardInnerImage.src = "pic/bbg.jpg";
      cardInnerImage.alt = "Default Background";
  
      const cardContent = document.createElement("div");
      cardContent.className = "card-content";
      cardContent.innerHTML = `
        <h4>Uploaded by: ${answer.uploader_name}</h4>
        <div class="button-group">
          <a style="text-decoration: none;" href="${answer.file_path}" download class="download-button">Download</a>
          ${
            answer.is_accepted
              ? '<p style="color: green;"><strong>✔ This answer was selected.</strong></p>'
              : `<form action="choose_answer.php" method="GET">
                  <input type="hidden" name="answer_id" value="${answer.id}">
                  <input type="hidden" name="quest_id" value="${questId}">
                  <input type="hidden" name="difficulty" value="${answer.difficulty}">
                  <button type="submit" class="accept-button">Choose This Answer</button>
                </form>`
          }
        </div>
      `;
  
      cardImage.appendChild(cardInnerImage);
      card.appendChild(cardImage);
      card.appendChild(cardContent);
      rightPane.appendChild(card);
    });
}
