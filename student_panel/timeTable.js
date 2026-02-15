document.addEventListener("DOMContentLoaded", () => {
  let timetableData = {};
  let currentDay = new Date().getDay(); // 0 = Sunday, 1 = Monday, etc.

  function loadStudentTimetable() {
    fetch("fetchTimetable.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "fetch=1"
    })
      .then(res => res.json())
      .then(data => {
        console.log("✅ Timetable data:", data);

        if (data.status === "success" && data.data) {
          timetableData = data.data;
          setData(currentDay, timetableData);
        } else {
          console.warn("⚠️ No timetable found for this student.");
        }
      })
      .catch(err => console.error("Error fetching timetable:", err));
  }

  function setData(dayIndex, timetableData) {
    const tbody = document.querySelector("table tbody");
    if (!tbody) return;

    tbody.innerHTML = "";

    const dayKeys = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"];
    const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    document.querySelector(".timetable div h2").innerHTML = dayNames[dayIndex];
    const dayKey = dayKeys[dayIndex];
    const dayArray = timetableData[dayKey] || [];

    if (dayArray.length === 0) {
      tbody.innerHTML = `<tr><td colspan="3" style="text-align:center;">No classes scheduled.</td></tr>`;
      return;
    }

    dayArray.forEach(item => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${item.start_time || ""}</td>
        <td>${item.end_time || ""}</td>
        <td>${item.subject || ""}</td>`;
      tbody.appendChild(row);
    });
  }

  document.getElementById("nextDay").onclick = () => {
    currentDay = (currentDay + 1) % 7;
    setData(currentDay, timetableData);
  };

  document.getElementById("prevDay").onclick = () => {
    currentDay = (currentDay - 1 + 7) % 7;
    setData(currentDay, timetableData);
  };

  loadStudentTimetable();
});