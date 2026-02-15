var dayOfWeak = 1;

var _class = "";
let _section = "";
let days = ["MONDAY", "TUESDAY", 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];

document.getElementById("editBtn").addEventListener("click", function () {
    document.querySelector('.editBtnBox').style.display = "none";
    document.querySelector('.saveBtnBox').style.display = "block";
});
document.getElementById("saveBtn").addEventListener("click", function () {
    document.querySelector('.saveBtnBox').style.display = "none";
    document.querySelector('.editBtnBox').style.display = "block";
});

document.getElementById("search-class").addEventListener("change", function() {
    const selectedClass = this.value;
    const sectionSelect = document.getElementById("search-section");

    // Habang naglo-load, i-disable muna at lagyan ng placeholder
    sectionSelect.innerHTML = '<option value="" selected disabled>Loading...</option>';

    if (selectedClass === "") {
        sectionSelect.innerHTML = '<option value="" selected disabled>Select Class First</option>';
        return;
    }

    // Tawagin ang PHP script para sa sections
    fetch(`../assets/get_sections.php?class=${encodeURIComponent(selectedClass)}`)
        .then(response => response.json())
        .then(data => {
            sectionSelect.innerHTML = '<option value="" selected disabled>Select Section</option>';
            
            if (data.status === 'success' && data.sections.length > 0) {
                data.sections.forEach(section => {
                    const option = document.createElement("option");
                    option.value = section;
                    option.textContent = section;
                    sectionSelect.appendChild(option);
                });
            } else {
                sectionSelect.innerHTML = '<option value="" selected disabled>No Section Found</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            sectionSelect.innerHTML = '<option value="" selected disabled>Error loading sections</option>';
        });
});

document.addEventListener("DOMContentLoaded", function () {
    _class = document.getElementById("search-class").value;
    _section = document.getElementById("search-section").value;

    loadTimeTable();
});
document.getElementById("findTimeTableBtn").addEventListener("click", () => {
    _class = document.getElementById("search-class").value;
    _section = document.getElementById("search-section").value;

    loadTimeTable();
});
function loadTimeTable() {

    document.getElementById("findTimeTableBtn").disabled = true;
    let sendObject = {
        dayOfWeak: dayOfWeak,
        class: _class,
        section: _section
    }
  
    fetch('../assets/fetchTimeTable.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: JSON.stringify(sendObject),
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            document.getElementById("findTimeTableBtn").disabled = false;
            document.getElementById('timeTableClassSection').innerHTML = "Class " + _class + " " + _section;
            document.getElementById("__day__").innerHTML = days[dayOfWeak - 1];


            if (data['status'] === 'success') {

                document.getElementById("lastEditor").innerHTML = data['editorName'];
                document.getElementById("editingTime").innerHTML = data['editingTime'];

                if (data['day'] === 'sunday') {
                    document.getElementById("timeTable_table1").innerHTML = "";
                    document.getElementById("timeTable_table2").innerHTML = "";
                    document.getElementById("lunch-alert").style.display = "none";
                    document.getElementById("dataNotAvailable").style.display = 'block';
                    document.getElementById("saveBtn").disabled = true;
                    document.getElementById("editBtn").disabled = true;

                } else {
                    document.getElementById("dataNotAvailable").style.display = 'none';
                    document.getElementById("timeTable_table1").innerHTML = data['table1Message'];
                    document.getElementById("lunch-alert").style.display = "block";
                    document.getElementById("timeTable_table2").innerHTML = data['table2Message'];
                    document.getElementById("saveBtn").disabled = false;
                    document.getElementById("editBtn").disabled = false;
                }


            }
            else if (data['status'] === "creating") {
                loadTimeTable();

            } else {
                document.getElementById("lunch-alert").style.display = "none";
                document.getElementById("timeTable_table1").innerHTML = "";
                document.getElementById("timeTable_table2").innerHTML = "";
                document.getElementById("lastEditor").innerHTML = "";
                document.getElementById("editingTime").innerHTML = "";
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });

}

document.getElementById('next-page-btn').addEventListener("click", () => {

    dayOfWeak += 1;
    if (dayOfWeak > 7) {
        dayOfWeak = 1;
    }

    loadTimeTable();

});

document.getElementById('prev-page-btn').addEventListener("click", () => {

    dayOfWeak -= 1;
    if (dayOfWeak < 1) {
        dayOfWeak = 7;
    }
    loadTimeTable();

});


// start going to make table editable

document.getElementById("editBtn").addEventListener('click', () => {
    const table1 = document.querySelectorAll("#timeTable_table1 td");

    for (let currentData = 0; currentData < table1.length; currentData++) {
        table1[currentData].querySelector(".tableInput").disabled = false;
    }

    const table2 = document.querySelectorAll("#timeTable_table2 td");

    for (let currentData = 0; currentData < table2.length; currentData++) {
        table2[currentData].querySelector(".tableInput").disabled = false;
    }

});

document.getElementById("saveBtn").addEventListener("click", () => {
  const rows = document.querySelectorAll(".tableRow");
  const updatedData = [];

  rows.forEach(row => {
    updatedData.push({
      rowId: row.dataset.id,
      startTime: row.querySelector(".startTime_").value,
      endTime: row.querySelector(".endTime_").value,
      subject: row.querySelector(".subject_").value
    });
  });

  const payload = {
    class: _class,
    section: _section,
    dayOfWeak: dayOfWeak,
    data: updatedData
  };

  fetch("../assets/updateTimeTable.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(res => {
    console.log("🟩 Update Response:", res);
    if (res.status === "success") {
      alert("✅ Timetable updated!");
      loadTimeTable(); // reload to show new values
    } else {
      alert("⚠️ Failed to save: " + res.message);
    }
  })
  .catch(err => console.error("Error saving timetable:", err));
});