//////////////////////////////////////////////////////////////////////////////////////
// Auto Attendance Bolock v2.1
//										by Fumi.Iseki  2013/05/07
//										mailto:iseki@solar-system.tuis.ac.jp
//										http://www.nsl.tuis.ac.jp
// 
//	This block/module are modified from Attendance block/module v1.0.8
//											by Dmitry Pupinin, Novosibirsk, Russia.


1. Overview

 This autoattend block is modification of the Attendance block by Mr. Dmitry Pupinin et. al.
 In addition to the original manual mode, automatic attendance mode (from the access log of Moodle) and semi-automatic 
attendance mode (user clicks a link) are also possible.

 I strongly recommend a combination of autoattendance block (autoattend) and autoattendance module (autoattendmod).


2. Functions

 This is the block for taking attendance. 
 The taking of attendance, there are three modes of manual, automatic and semi-automatic.

* Automatic mode
  Attendance is taken automatically when the student has access to the course.
  - As the access log is checked by cron, when you want to get the newest information, time lag will occur.
  - It is also possible to obtain the newest information by clicking the "Refresh" button manually.
  - You can add a restriction by IP address.
  - You can also prohibit the attendance from the same PC.
  - If log of Moodle is left, you can retry to attend at any time.

* Semi-automatic mode
  You take attendance by the student clicks the link of attendance.
  - It is required that the student clicks the link of attendance.
  - You can confirm the attendance in real time.
  - You can add a restriction by IP address and/or key words.
  - You can also prohibit the attendance from the same PC.
  - If you must have installed the attendance module (autoattendmod), it will be in attendance link instead.
  - If you have not installed the attendance module, it is necessary to put paste the following 
    link as an attendee link.
         MOODLE_URL/blocks/autoattend/semiautobutton.php?course=[course id]
  - If there is no class of semi-automatic mode is taking attendance in the appropriate time, attendance link 
    displays the detailed data of attendance.

* Manual mode
  Teachers will record by taking the roll call manually. It is flexibil, but cumbersome.
  - Please use in the classroom do not use the computer or use in the correction after taking the automatic and semi-automatic mode


3. etc.

 Please see also http://www.nsl.tuis.ac.jp/xoops/modules/xpwiki/?autoattend%20%28E%29

