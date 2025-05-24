// data.js - Final Version with Integrated Add Student Functionality

document.addEventListener('DOMContentLoaded', function () {

    // Add Student Modal Functionality
    const addStudentBtn = document.getElementById('addStudentBtn');
    if (addStudentBtn) {
        addStudentBtn.addEventListener('click', function () {
            // Reset form when opening modal
            const form = document.getElementById('studentForm');
            if (form) form.reset();
            const modal = new bootstrap.Modal(document.getElementById('addStudentModal'));
            modal.show();
        });
    }

    // Save Student Function
    const saveStudentBtn = document.getElementById('saveStudentBtn');
    if (saveStudentBtn) {
        saveStudentBtn.addEventListener('click', async function () {
            const btn = this;
            const originalText = btn.innerHTML;

            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';
            btn.disabled = true;

            try {
                const form = document.getElementById('studentForm');
                if (!form.checkValidity()) {
                    throw new Error('Please fill in all required fields');
                }

                const studentData = {
                    student_id: document.getElementById('studentId').value.trim(),
                    last_name: document.getElementById('lastName').value.trim(),
                    first_name: document.getElementById('firstName').value.trim(),
                    middle_name: document.getElementById('middleName').value.trim(),
                    course: document.getElementById('course').value,
                    year: document.getElementById('year').value
                };

                const response = await fetch('../back_end/add_student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(studentData)
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to save student');
                }

                alert('Student added successfully!');
                bootstrap.Modal.getInstance(document.getElementById('addStudentModal')).hide();
                loadStudentData();

            } catch (error) {
                alert(`Error: ${error.message}`);
                console.error('Error:', error);
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }


    // Show modal when import button is clicked
    const importBtn = document.getElementById('importBtn');
    if (importBtn) {
        importBtn.addEventListener('click', function () {
            const myModal = new bootstrap.Modal(document.getElementById('importModal'));
            myModal.show();
        });
    }

    // Load student data when page loads
    loadStudentData();
});

// Excel file import function
async function uploadExcelFile() {
    const fileInput = document.getElementById('excelFileInput');
    const file = fileInput.files[0];

    if (!file) {
        alert('Please select an Excel file first.');
        return;
    }

    const uploadBtn = document.querySelector('#importModal .modal-footer button');
    const originalBtnText = uploadBtn.innerHTML;
    uploadBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1 spinner"></i> Processing...';
    uploadBtn.disabled = true;

    try {
        const data = await file.arrayBuffer();
        const workbook = XLSX.read(data);
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const jsonData = XLSX.utils.sheet_to_json(firstSheet);

        if (jsonData.length === 0) throw new Error('Excel file contains no data');

        const firstRow = jsonData[0];
        const hasRequiredColumns =
            Object.keys(firstRow).some(k => k.toLowerCase().includes('student id')) &&
            Object.keys(firstRow).some(k => k.toLowerCase().includes('last name')) &&
            Object.keys(firstRow).some(k => k.toLowerCase().includes('first name'));

        if (!hasRequiredColumns) {
            throw new Error('Excel file must contain columns: Student ID, Last Name, and First Name');
        }

        const processedData = jsonData.map(row => {
            const studentIdKey = Object.keys(row).find(k => k.toLowerCase().includes('student id')) || 'Student ID';
            const lastNameKey = Object.keys(row).find(k => k.toLowerCase().includes('last name')) || 'Last Name';
            const firstNameKey = Object.keys(row).find(k => k.toLowerCase().includes('first name')) || 'First Name';
            const middleNameKey = Object.keys(row).find(k => k.toLowerCase().includes('middle name')) || 'Middle Name';
            const courseKey = Object.keys(row).find(k => k.toLowerCase().includes('course')) || 'Course';
            const yearKey = Object.keys(row).find(k => k.toLowerCase().includes('year')) || 'Year';

            return {
                student_id: String(row[studentIdKey] || '').trim(),
                last_name: String(row[lastNameKey] || '').trim(),
                first_name: String(row[firstNameKey] || '').trim(),
                middle_name: String(row[middleNameKey] || '').trim(),
                course: String(row[courseKey] || '').trim(),
                year: String(row[yearKey] || '').trim()
            };
        }).filter(student => student.student_id && student.last_name && student.first_name);

        if (processedData.length === 0) throw new Error('No valid student records found after processing');

        const response = await fetch('../back_end/import_students.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(processedData)
        });

        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Server returned an error');

        alert(`Successfully imported ${result.insertedCount} student records!`);
        bootstrap.Modal.getInstance(document.getElementById('importModal')).hide();
        location.reload();

    } catch (error) {
        console.error('Import error:', error);
        alert(`Import failed: ${error.message}`);
    } finally {
        uploadBtn.innerHTML = originalBtnText;
        uploadBtn.disabled = false;
    }
}

// Load Student Data Function
async function loadStudentData() {
    try {
        const response = await fetch('../back_end/fetch_students.php');
        const data = await response.json();

        if (!response.ok || data.error) {
            throw new Error(data.error || 'Failed to fetch student data');
        }

        const tbody = document.querySelector('.table-body-container tbody');
        tbody.innerHTML = '';

        if (data.students.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-database-exclamation me-2"></i>
                        No student records found
                    </td>
                </tr>
            `;
            return;
        }

        data.students.forEach(student => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${student.student_id}</td>
                <td>${student.last_name}</td>
                <td>${student.first_name}</td>
                <td>${student.middle_name || ''}</td>
                <td>${student.course || ''}</td>
                <td>${student.year || ''}</td>
                <td>
                    <span class="badge ${student.status === 'Voted' ? 'bg-success' : 'bg-secondary'}">
                        ${student.status || 'Not Voted'}
                    </span>
                </td>
            `;
            tbody.appendChild(row);
        });

        // Optional: Match header widths
        const headerCells = document.querySelectorAll('.table-container > table thead th');
        const bodyCells = document.querySelectorAll('.table-body-container table tbody td');

        headerCells.forEach((headerCell, index) => {
            const width = headerCell.offsetWidth;
            Array.from(bodyCells)
                .filter((_, i) => i % headerCells.length === index)
                .forEach(cell => {
                    cell.style.minWidth = `${width}px`;
                });
        });

    } catch (error) {
        console.error('Error loading student data:', error);
        const tbody = document.querySelector('.table-body-container tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    ${error.message}
                </td>
            </tr>
        `;
    }
}

document.getElementById('exportBtn').addEventListener('click', async function () {
    try {
        // Show loading indicator
        const exportBtn = this;
        exportBtn.disabled = true;
        exportBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Exporting...';

        const response = await fetch('../back_end/fetch_students.php');
        const result = await response.json();

        if (!response.ok || result.error) throw new Error(result.error || 'Failed to fetch data');

        const students = result.students;
        if (!students || students.length === 0) {
            alert('No student data to export.');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Add logo and header
        const imgData = await getBase64Image('../images/cslogo.png');
        doc.addImage(imgData, 'PNG', 14, 10, 20, 20);
        doc.setFontSize(18);
        doc.setTextColor(40, 53, 147);
        doc.setFont('helvetica', 'bold');
        doc.text('Philippine Colege of Science and Technology', 105, 20, { align: 'center' });
        doc.setFontSize(14);
        doc.text('College of Computer Studies', 105, 28, { align: 'center' });
        doc.setFontSize(12);
        doc.setTextColor(100);
        doc.text('Student Voting Records', 105, 35, { align: 'center' });
        
        // Add current date
        const today = new Date();
        doc.setFontSize(10);
        doc.text(`Generated on: ${today.toLocaleDateString()}`, 14, 40);

        const yearLabels = {
            '1': 'First Year',
            '2': 'Second Year',
            '3': 'Third Year',
            '4': 'Fourth Year'
        };
        const years = Object.keys(yearLabels);
        let isFirstPage = true;

        years.forEach((year, i) => {
            if (!isFirstPage) doc.addPage();
            isFirstPage = false;
            
            const yearStudents = students.filter(s => s.year === year);
            const voted = yearStudents.filter(s => s.status?.toLowerCase() === 'voted');
            const notVoted = yearStudents.filter(s => s.status?.toLowerCase() !== 'voted');

            doc.setFontSize(16);
            doc.setTextColor(40, 53, 147);
            doc.text(`${yearLabels[year]}`, 14, 50);

            if (voted.length > 0) {
                doc.setFontSize(12);
                doc.setTextColor(25, 118, 210);
                doc.text("Voted Students", 14, 60);
                doc.autoTable({
                    startY: 65,
                    head: [['ID', 'Last Name', 'First Name', 'Middle Name', 'Course']],
                    headStyles: {
                        fillColor: [40, 53, 147],
                        textColor: 255,
                        fontStyle: 'bold'
                    },
                    body: voted.map(s => [s.student_id, s.last_name, s.first_name, s.middle_name, s.course]),
                    theme: 'grid',
                    styles: {
                        cellPadding: 3,
                        fontSize: 10,
                        valign: 'middle'
                    },
                    alternateRowStyles: {
                        fillColor: [240, 240, 240]
                    }
                });
            }

            if (notVoted.length > 0) {
                const startY = doc.lastAutoTable ? doc.lastAutoTable.finalY + 15 : 60;
                doc.setFontSize(12);
                doc.setTextColor(198, 40, 40);
                doc.text("Not Voted", 14, startY);
                doc.autoTable({
                    startY: startY + 5,
                    head: [['ID', 'Last Name', 'First Name', 'Middle Name', 'Course']],
                    headStyles: {
                        fillColor: [183, 28, 28],
                        textColor: 255,
                        fontStyle: 'bold'
                    },
                    body: notVoted.map(s => [s.student_id, s.last_name, s.first_name, s.middle_name, s.course]),
                    theme: 'grid',
                    styles: {
                        cellPadding: 3,
                        fontSize: 10,
                        valign: 'middle'
                    },
                    alternateRowStyles: {
                        fillColor: [255, 235, 235]
                    }
                });
            }
        });

        // Add footer
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text(`Page ${i} of ${pageCount}`, 105, 285, { align: 'center' });
            doc.text('Confidential - For Internal Use Only', 105, 290, { align: 'center' });
        }

        doc.save(`Student_Voting_Records_${today.toISOString().slice(0,10)}.pdf`);
    } catch (error) {
        console.error("Export Error:", error);
        alert("Failed to export data: " + error.message);
    } finally {
        // Reset button state
        const exportBtn = document.getElementById('exportBtn');
        exportBtn.disabled = false;
        exportBtn.innerHTML = '<i class="fa fa-file-pdf"></i> Export to PDF';
    }
});

// Helper function to convert image to base64
function getBase64Image(url) {
    return new Promise((resolve) => {
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = function() {
            const canvas = document.createElement('canvas');
            canvas.width = this.naturalWidth;
            canvas.height = this.naturalHeight;
            canvas.getContext('2d').drawImage(this, 0, 0);
            resolve(canvas.toDataURL('image/png'));
        };
        img.src = url;
    });
}
