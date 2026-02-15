var generalFormData;
var personalFormData;
var guardianFormData;
var personalsNextBtnClicked = false;
var guardiansNextBtnClicked = false;
var editing = false;
var editingTeacherId = "";
var preEditedData;
var postEditedData;

// page settings
var beginIndex = 0;
var limit = 10;
var counter = 1;

/** * REVISION 1: Mapping ng Sections 
 * Dito kinukuha ng dropdown kung anong sections ang dapat lumabas base sa piniling Class.
 */
const sectionData = {
    "12c": ["Sylvester", "Leo", "Jerome"],
    "11c": ["Mark", "John", "Luke"],
    "10": ["Section A", "Section B"],
    "9": ["Section 9-A", "Section 9-B"],
    "8": ["Section 8-A"],
    "7": ["Section 7-A"],
    "6": ["Section 6-A"],
    "5": ["Section 5-A"],
    "4": ["Section 4-A"],
    "3": ["Section 3-A"],
    "2": ["Section 2-A"],
    "1": ["Section 1-A"],
    "pg": ["Nursery Section"]
};

function populateSections(classVal, targetId, isSearch = false) {
    const dropdown = document.getElementById(targetId);
    if (!dropdown) return;
    
    dropdown.innerHTML = isSearch ? '<option value="">All Section</option>' : '<option value="" selected disabled>Select Section</option>';
    
    if (sectionData[classVal]) {
        sectionData[classVal].forEach(s => {
            const opt = document.createElement("option");
            opt.value = s;
            opt.textContent = s;
            dropdown.appendChild(opt);
        });
    }
}

document.addEventListener('DOMContentLoaded', function(){
    showStudents();

    /** * REVISION 2: Event Listeners para sa Class Dropdowns
     * Ikinabit sa tamang IDs base sa iyong student.php (modal-class at search-class)
     */
    
    // Para sa Search Filters sa Main Page
    const searchClass = document.getElementById("search-class");
    if(searchClass){
        searchClass.addEventListener("change", function() {
            populateSections(this.value, "search-section", true);
            searchFunction(); 
        });
    }

    // Para sa Modal (Add/Edit Student)
    const modalClass = document.getElementById("modal-class");
    if(modalClass){
        modalClass.addEventListener("change", function() {
            populateSections(this.value, "modal-section", false);
        });
    }
});


document.getElementById('addTeacherButton').addEventListener('click', function () {
    editing = false;
    cleanForm();
});
document.getElementById("add_student_dropdown").addEventListener("click", function(){
    editing = false;
    cleanForm();
});
document.getElementById("remove-student-jumbo-btn").addEventListener("click", function(){
    document.querySelector(".remove_student_id").value = "";
});
document.getElementById("remove_student_dropdown").addEventListener("click", function(){
    document.querySelector(".remove_student_id").value = "";
});

(() => {
    'use strict';

    let gInfoBtn = document.getElementById("general-info-btn");
    let genform = document.querySelector('#general-form');

    gInfoBtn.addEventListener('click', event => {
        validateGeneralForm();

        event.preventDefault();
        event.stopPropagation();
    }, false);

    function validateGeneralForm() {
        if (genform.checkValidity()) {

            const input = document.getElementById('uploadImage');
            const file = input.files[0];
        
            const formElement = document.querySelector('#general-form');
           
            const formData = new FormData(formElement);
            const imageInput = document.getElementById('uploadImage');
            const imageFile = imageInput.files[0];
            if (imageFile) {
                formData.append('image', imageFile);
            }
            generalFormData = formData;
        
            $("#addTeacherModal").modal("hide");
            $("#personalInformationModal").modal("show");
        } else {
            genform.classList.add('was-validated');
        }
    }

    const pInfoBtn = document.getElementById('personal-info-btn');
    const personalform = document.querySelector('#personal-form');

    pInfoBtn.addEventListener('click', event => {
        validatePhoneNumber("phone");
        validatePersonalForm();

        personalsNextBtnClicked = true;
        event.preventDefault();
        event.stopPropagation();
    }, false);

    function validatePersonalForm() {
        if (personalform.checkValidity()) {

            const formElement1 = document.querySelector('#personal-form');
            personalFormData = Object.fromEntries(new FormData(formElement1).entries());

            const formData1 = new FormData(formElement1);
            personalFormData = formData1;
            
         
            $("#personalInformationModal").modal("hide");
            $("#guardian_information").modal("show");
        } else {
            personalform.classList.add('was-validated');
        }
    }

    const guardianBtn = document.getElementById('guardian-form-btn');
    const guardianform = document.querySelector('#guradian-form');

    guardianBtn.addEventListener('click', event => {
        validatePhoneNumber("gphone");
        validateGuradianForm();

        guardiansNextBtnClicked = true;
        event.preventDefault();
        event.stopPropagation();
    }, false);

    function validateGuradianForm() {
        if (guardianform.checkValidity()) {

            const formElement2 = document.querySelector('#guradian-form');
            const formData1 = new FormData(formElement2);
            guardianFormData = formData1;

            if(editing){
                generalFormData.delete("image");
            }

            let fullFormData = new FormData();

            for (const [key, value] of generalFormData.entries()) {
                fullFormData.append(key, value);
            }
            for (const [key, value] of personalFormData.entries()) {
                fullFormData.append(key, value);
            }
            for (const [key, value] of guardianFormData.entries()) {
                fullFormData.append(key, value);
            }

            if (!editing) {
                sendDataToServer(fullFormData);
            } else {
                let myToast = new bootstrap.Toast(document.getElementById('liveToast'));
                let liveToast = document.getElementById("liveToast");

                if(areFormDataEqual(fullFormData, preEditedData)){
                    liveToast.style.backgroundColor = "#BBF7D0";
                    liveToast.style.color = 'green';
                    document.getElementById('toast-alert-message').innerHTML = "Nothing edited!";
                    
                    $('#addTeacherModal').modal('hide');
                    myToast.show(); 
                } else {
                    postEditedData = fullFormData;
                    postEditedData.append('id',preEditedData.get('id'));

                    $('#guardian_information').modal("hide"); 
                    $("#edit-confirmation-modal").modal("show");
                }

                editTeacherById(editingTeacherId);
                $("#guardian_information").modal("hide");
                cleanForm();
            }
            $("#guardian_information").modal("hide");

        } else {
            guardianform.classList.add('was-validated');
        }
    }

    document.getElementById("confirm-edit-btn").addEventListener('click', event => {
        let myToast = new bootstrap.Toast(document.getElementById('liveToast'));
        let liveToast = document.getElementById("liveToast");
        fetch("../assets/editStudent.php", {
            method: 'POST',
            body: postEditedData,
        })
            .then(response => response.text())
            .then(data => {
                if (data.indexOf("success") !== -1) {
                    liveToast.style.backgroundColor = "#BBF7D0";
                    liveToast.style.color = 'green';
                    document.getElementById('toast-alert-message').innerHTML = "Details edited successfully";
                    cleanForm();
                } else {
                    liveToast.style.backgroundColor = "#FECDD3";
                    liveToast.style.color = 'red';
                    document.getElementById('toast-alert-message').innerHTML = data;
                    $("#personalInformationModal").modal("show");
                }
                myToast.show();
            })
            .catch(error => {
                console.error("Error:", error);
            });

        $("#edit-confirmation-modal").modal("hide");
        showStudents();

    }, false);

    function areFormDataEqual(formDataSubset, formDataSuperset) {
        for (const entry of formDataSubset.entries()) {
            const [key, value] = entry;
            if (!formDataSuperset.has(key)) return false;
            if (formDataSuperset.get(key) !== value) return false;
        }
        return true;
    }
})();

function validatePhoneNumber(id) {
    var phoneNumberInput = document.getElementById(id);
    var phoneNumberRegex = /^\d{10}$/; 

    if (phoneNumberRegex.test(phoneNumberInput.value)) {
        phoneNumberInput.setCustomValidity('');
        phoneNumberInput.parentNode.querySelector('.invalid-feedback').innerHTML = '';
    } else {
        phoneNumberInput.setCustomValidity('Please enter a valid 10-digit phone number.');
        phoneNumberInput.parentNode.querySelector('.invalid-feedback').innerHTML = 'Please enter a valid 10-digit phone number.';
        phoneNumberInput.reportValidity();
    }
}

document.getElementById("phone").addEventListener('keyup', function () {
    if (personalsNextBtnClicked) { validatePhoneNumber("phone"); }
});
document.getElementById("gphone").addEventListener('keyup', function () {
    if (guardiansNextBtnClicked) { validatePhoneNumber("gphone"); }
});

function sendDataToServer(formData) {
    var phpScript = "../assets/addStudent.php";
    let myToast = new bootstrap.Toast(document.getElementById('liveToast'));
    let liveToast = document.getElementById("liveToast");

    fetch(phpScript, { method: 'POST', body: formData })
        .then(response => response.text())
        .then(data => {
            if (data.indexOf("success") !== -1) {
                liveToast.style.backgroundColor = "#BBF7D0";
                liveToast.style.color = 'green';
                document.getElementById('toast-alert-message').innerHTML = "Student successfully added";
                cleanForm();
            } else {
                liveToast.style.backgroundColor = "#FECDD3";
                liveToast.style.color = 'red';
                document.getElementById('toast-alert-message').innerHTML = data;
                $("#personalInformationModal").modal("show");
            }
            myToast.show();
        })
        .catch(error => console.error("Error:", error));
}

function cleanForm() {
    var genForm = document.getElementById('general-form');
    var perForm = document.getElementById('personal-form');
    var gurForm = document.getElementById('guradian-form');

    Array.from(genForm.elements).forEach(element => element.value = "");
    Array.from(perForm.elements).forEach(element => element.value = "");
    Array.from(gurForm.elements).forEach(element => element.value = "");
    
    genForm.classList.remove('was-validated');
    perForm.classList.remove('was-validated');
    gurForm.classList.remove('was-validated');

    /** REVISION 3: Reset Section Text **/
    const modalSection = document.getElementById("modal-section");
    if(modalSection){
        modalSection.innerHTML = '<option value="" selected disabled>Select Class First</option>';
    }
}

// Remove student functions (Original code maintained)
var student_id = "";
function deleteStudentWithId(id) {
    student_id = id;
    $('#delete-confirmation-modal').modal('show');
}

function deleteTeacherWithIdSeted() {
    let myToast = new bootstrap.Toast(document.getElementById('liveToast'));
    let liveToast = document.getElementById("liveToast");

    fetch('../assets/removeStudent.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'studentid=' + encodeURIComponent(student_id),
    })
    .then(response => response.text())
    .then(data => {
        if (data.indexOf("success") != -1) {
            liveToast.style.backgroundColor = "#BBF7D0";
            liveToast.style.color = 'green';
            document.getElementById('toast-alert-message').innerHTML = "Student removed successfully";
        } else {
            liveToast.style.backgroundColor = "#FECDD3";
            liveToast.style.color = 'red';
            document.getElementById('toast-alert-message').innerHTML = data;
        }
        $('#delete-confirmation-modal').modal('hide');
        showStudents();
        myToast.show();
    });
}

function findAndshowStudents(){
    beginIndex = 0;
    counter = 1;
    showStudents();
}

document.addEventListener("DOMContentLoaded", function () {
    loadStudents(); // Tawagin ito agad pagka-load ng page
});

document.getElementById("findStudentsBtn").addEventListener("click", () => {
    loadStudents();
});

function loadStudents() {
    let searchClass = document.getElementById("search-class").value;
    let searchSection = document.getElementById("search-section").value;
    let tableContainer = document.getElementById("table-container");

    // I-fetch ang data mula sa iyong backend (halimbawa: fetchStudents.php)
    fetch('../assets/fetchStudents.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `class=${searchClass}&section=${searchSection}`
    })
    .then(response => response.text())
    .then(data => {
        tableContainer.innerHTML = data; // Dito ipapasok ang HTML table mula sa PHP
    })
    .catch(error => console.error('Error loading students:', error));
}

function showStudents() {
    document.getElementById("next-page-btn").classList.add('disabled');
    document.getElementById("prev-page-btn").classList.add('disabled');
 
    var tablebody = document.getElementById("teacher-table-body");
    var name = document.getElementById("search-teacher-name").value;
    var _class = document.getElementById("search-class").value;
    var _section = document.getElementById("search-section").value;

    var requestData = { name: name, as: _class, a: _section };
    fetch('../assets/fetchStudents.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData),
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("next-page-btn").classList.remove('disabled');
        document.getElementById("prev-page-btn").classList.remove('disabled');

        if((data[0] + "") === "No_Record"){
            tablebody.innerHTML = "";
            document.getElementById("dataNotAvailable").style.display = 'block';
            document.getElementById("next-page-btn").classList.add('disabled');
            document.getElementById("prev-page-btn").classList.add('disabled');
            document.getElementById("page-number").innerHTML = counter + "";
        }else{
            document.getElementById("dataNotAvailable").style.display = 'none';
            document.getElementById("page-number").innerHTML = counter + "";

            if ((beginIndex + limit) >= data.length) {
                document.getElementById("next-page-btn").classList.add('disabled');
            }
            if(beginIndex <= 0){
                document.getElementById("prev-page-btn").classList.add('disabled');
            }

            let students = "";
            let flag = 0;
            for (let i = beginIndex; i < data.length; i++) {
                if (flag >= limit) break;
                students += data[i];
                flag += 1;
            }
            tablebody.innerHTML = students;
        }
    });
}

document.getElementById("search-teacher-name").addEventListener("keyup", searchFunction);
document.getElementById("search-teacher-name").addEventListener("search", searchFunction);

function searchFunction(){
    beginIndex = 0;
    counter = 1;
    showStudents();
}

function editStudent(tid){
    editTeacherById(tid);
    if(editing){
        document.getElementById("uploadImageField").style.display = "none";
    }
    $('#addTeacherModal').modal('show');
}

function editTeacherById(sid) {
    cleanForm();
    editing = true;
    editingTeacherId = sid;

    fetch('../assets/fetchStudentInfo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(sid),
    })
    .then(response => response.json())
    .then(data => {
        preEditedData = new FormData();
        for (const key in data) {
            if (data.hasOwnProperty(key)) preEditedData.append(key, data[key]);
        }

        document.getElementById("fname").value = data['fname'];
        document.getElementById("lname").value = data['lname'];
        document.getElementById("father").value = data['father'];
        document.getElementById("gender").value = data['gender'];
        document.getElementById("dob").value = data['dob'];
        
        // REVISION 4: I-load ang section options bago i-set ang value sa Edit
        if(document.getElementById("modal-class")){
            document.getElementById("modal-class").value = data['class'];
            populateSections(data['class'], "modal-section", false);
            document.getElementById("modal-section").value = data['section'];
        }

        document.getElementById("phone").value = data['phone'];
        document.getElementById("email").value = data['email'];
        document.getElementById("address").value = data['address'];
        document.getElementById("city").value = data['city'];
        document.getElementById("zip").value = data['zip'];
        document.getElementById("state").value = data['state'];
        document.getElementById("guardian").value = data['guardian'];
        document.getElementById("gphone").value = data['gphone'];
        document.getElementById("gaddress").value = data['gaddress'];
        document.getElementById("gcity").value = data['gcity'];
        document.getElementById("gzip").value = data['gzip'];
        document.getElementById("relation").value = data['relation'];
    });
}

document.getElementById("prev-page-btn").addEventListener('click', function () {
    beginIndex -= limit;
    showStudents();
    counter -= 1;
});
document.getElementById("next-page-btn").addEventListener('click', function () {
    beginIndex += limit;
    showStudents();
    counter += 1;
});

function AddStudentBtnClick(){
    editing = false;
    if(!editing) document.getElementById("uploadImageField").style.display = "block";
}

function backToStudentDetail(){
    $("#personalInformationModal").modal('hide');
    $("#addTeacherModal").modal('show');
}

function backToAddressDetail(){
    $("#guardian_information").modal('hide');
    $("#personalInformationModal").modal('show');
}

// feedback logic
document.getElementById("feedback-search-class").addEventListener('change', () => {
    let classSection = getClassSectionForFeedback();
    /** REVISION 5: Feedback Section Dropdown Update **/
    populateSections(classSection['class'], "feedback-search-section", false);
    getStudents(classSection['class'], classSection['section']);
});

document.getElementById("feedback-search-section").addEventListener('change', () => {
    let classSection = getClassSectionForFeedback();
    getStudents(classSection['class'], classSection['section']);
});

document.getElementById("feedback-students-tab").addEventListener('click', () => {
    let classSection = getClassSectionForFeedback();
    getStudents(classSection['class'], classSection['section']);
});

function getStudents(_class, _section) {
    let classSection = { class: _class + "", section: _section + "" };
    fetch("../assets/getStudentSelection.php", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(classSection),
    })
    .then(response => response.json())
    .then(data => {
        if (data['status'] === 'success') {
            document.getElementById("feedback-search-student").innerHTML = data['content'];
        } else {
            document.getElementById("feedback-search-student").innerHTML = "<option selected disabled value=''>--select--</option>";
        }
    });
}

function getClassSectionForFeedback() {
    return {
        class: document.getElementById("feedback-search-class").value,
        section: document.getElementById("feedback-search-section").value
    };
}

function findStudentFeedback() {
    let id = document.getElementById("feedback-search-student").value;
    if (id === "") {
        document.getElementById("select-student-first").style.display = "block";
    } else {
        document.getElementById("select-student-first").style.display = "none";
        getStudentsFeedbacks(id);
    }
}

function getStudentsFeedbacks(id) {
    fetch('../assets/getStudentDetailsAndFeedback.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id),
    })
    .then(response => response.json())
    .then(data => {
        if (data['status'] === 'success') {
            document.querySelector(".student-feedback").style.display = "block";
            document.getElementById("not-selected-feedbacks").style.display = "none";
            document.querySelector(".feedback-student-name").innerHTML = data['name'];
            document.getElementById("feedback-student-id").innerHTML = "<b>ID</b> - " + data['id'];
            document.getElementById("feedback-student-phone").innerHTML = "<b>Phone</b> - " + data['phone'];
            document.getElementById("feedback-student-dob").innerHTML = "<b>DOB</b> - " + data['dob'];
            document.getElementById("feedback-student-pic").src = data['image'];
            document.getElementById("reciver-student-id").value = data['id'];
            let msgbox = document.getElementById("feedback-message-box");
            msgbox.innerHTML = data['feedbacks'];
            msgbox.scrollTop = msgbox.scrollHeight;
        } else {
            document.querySelector(".student-feedback").style.display = "none";
            document.getElementById("not-selected-feedbacks").style.display = "block";
        }
    });
}

document.getElementById('send-feedback-btn').addEventListener("click", function () {
    let msg = document.getElementById('feedback-msg').value + "";
    if (msg.trim() === "") {
        document.getElementById("empty-message-alert").style.display = "block";
    } else {
        let receiver = document.getElementById("reciver-student-id").value;
        sendFeedback(receiver, msg);
    }
});

function sendFeedback(receiver, msg) {
    let messageObject = { receiver: receiver + "", message: msg + "" };
    let myToast = new bootstrap.Toast(document.getElementById('liveToast'));
    let liveToast = document.getElementById("liveToast");

    fetch("../assets/sendFeedback.php", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(messageObject),
    })
    .then(response => response.json())
    .then(data => {
        if (data['status'] === 'success') {
            document.getElementById('feedback-msg').value = "";
        } else {
            liveToast.style.backgroundColor = "#FECDD3";
            liveToast.style.color = 'red';
            document.getElementById('toast-alert-message').innerHTML = data['msg'];
            myToast.show();
        }       
        getStudentsFeedbacks(receiver);
    });
}

function deleteFeedback(feedbackid, receiverID){
    let myToast = new bootstrap.Toast(document.getElementById('liveToast'));
    let liveToast = document.getElementById("liveToast");

    fetch('../assets/deleteFeedbackWithId.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'feedbackid=' + encodeURIComponent(feedbackid),
    })
    .then(response => response.json())
    .then(data => {
        if (data['status'] === 'success') {
            liveToast.style.backgroundColor = "#BBF7D0";
            liveToast.style.color = 'green';
        } else {
            liveToast.style.backgroundColor = "#FECDD3";
            liveToast.style.color = 'red';
        }
        document.getElementById('toast-alert-message').innerHTML = data['message'];
        myToast.show();
        getStudentsFeedbacks(receiverID);
    });
}

document.getElementById("feedback-msg").addEventListener('keyup', function(){
    document.getElementById("empty-message-alert").style.display = 'none';
});

document.getElementById("feedback-students-tab").addEventListener("click", ()=>{
    document.querySelector(".student-feedback").style.display = "none";
    document.getElementById("not-selected-feedbacks").style.display = "block";
});

$(document).ready(function(){
    $("body").scrollTop(0);
});