<div style="text-align: center;"><strong>CONTRACT &nbsp;No.&nbsp;<u>&nbsp; &nbsp;
    <?=$this->row->clientAgreementNumber?>
    &nbsp;&nbsp;</u> от &nbsp; &nbsp;&laquo;
    <?=substr($this->row->manageDate, 8, 2)?>
    &raquo;&nbsp;<u>&nbsp; &nbsp; &nbsp;
    <?=$this->row->ldate('manageDate', 'F')?>
    &nbsp;&nbsp;</u>&nbsp;
    <?=$this->row->date('manageDate', 'Y')?>
    <br />
    for holding a festive event in the children&#39;s entertainment center &quot;Kangaroo&quot;</strong>
</div><br />

<div style="text-align: justify;">
    LLC &quot;Carousel-NN&quot;, represented by Director Mikhalko NS, acting under the Charter, hereinafter referred to as &quot;Contractor&quot; on the one hand, and
    <strong>
        <u>&nbsp; &nbsp;
            <?=$this->row->clientTitle?>
            &nbsp; &nbsp;
        </u>
    </strong>
    called (ND) in the &quot;Customer&quot;, on the other hand, have concluded this Agreement as follows:
</div>

<div style="margin-left: 20px; text-align: center;"><strong>1. Subject of the contract</strong></div>
<div style="text-align: justify;">
    1.1. Under the present contract the Contractor undertakes to provide the Customer with services for organizing and conducting a variety of festive events (organization of birthdays, holidays) in the territory of&nbsp;<strong>the children&#39;s entertainment center &quot;Kangaroo&quot; Nizhny Novgorod, <?=$this->row->foreign('districtId')->address?>, phone <?=$this->row->foreign('districtId')->phone?></strong>,&nbsp;hereinafter referred to as an event.&nbsp;<br />
    1.2. Terms of the Event are determined by the Application specified in clause 5 of this agreement.
</div><br />

<div style="text-align: center;"><strong>2. The amount of the contract and the procedure for settlements</strong></div>
<div style="text-align: justify;">
    2.1. The total cost of services is determined on the basis of the Order approved by the parties, clause 5 and valid at the time of conclusion of the contract of approved rates.<br />
    2.2. Simultaneously with the conclusion of the contract, the Customer pays an advance payment of 500 rubles.<br />
    2.3. The remaining cost of services is paid directly before the start of the Event.<br />
    2.4. The cost of visiting attractions in the total cost of services is not included.<br />
    2.5. Serving of the festive table remains at the discretion of the Customer and is not included in the cost of services.<br />
    2.6. The birthday party is provided with a free visit to attractions for the duration of the event.
</div>

</br><div style="text-align: center;"><strong>3. Rights and obligations of the parties</strong></div>
<div style="text-align: justify;">
    3.1. The Contractor has the right to refuse to the Customer in carrying out the Event in case of non-compliance with the terms of this agreement or violation of public order.<br />
    3.2. The Contractor undertakes to provide the Customer with services for carrying out the Event in accordance with the application of cl.<br />
    3.3. The Customer does not have the right to invite third parties who are not employees of Karusel-NN LLC to carry out the Event.<br />
    3.4. If the start of the Event is late due to the fault of the Customer, the Contractor has the right to refuse to extend the time specified in the Application.<br />
<strong>3.5. The Parties undertake to timely inform about any changes, additions and circumstances that impede the Event not later than 5 days before the date of the Event.<br />
    3.6. Date or time of the event can be changed at the initiative of the Customer no later than 5 days before the event.<br />
    3.7. In the event of the Customer&#39;s refusal to carry out the Event or termination of the contract less than 5 days prior to the date of the Event, the prepayment is not refundable.<br />
    3.8. In the event that the Customer refuses to carry out the Event or terminates the agreement more than 5 days before the event, the Contractor undertakes to return the prepayment in full.<br />
</strong>3.9. If the Contractor changes tariffs and quotations, the contract amount does not change.
</div>

</br><div style="text-align: center;"><strong>4. Other conditions</strong></div>

<div style="text-align: justify;">
    4.1. Activities are held during the work of the children&#39;s entertainment center &quot;Kangaroo&quot;, from 10-00 to 20-00.<br />
    4.2. The duration of the Event is 2 hours, the animation program lasts 1 hour and starts at the same time as the beginning of the Event, unless otherwise specified in the Agreement.<br />
    4.3. The time of the event can not be different from the time of the work of the children&#39;s entertainment center &quot;Kangaroo&quot;.<br />
    4.4. On the territory of the children&#39;s &quot;Kangaroo&quot; entertainment center&nbsp;<i>is strictly prohibited: drink alcoholic beverages, use for events pyrotechnics, flammable and explosive substances, as well as any other items that can harm the health of visitors.</i><br />
    4.5. During the event, the total number of adults should not exceed the number of children.<br />
    4.6. The Contractor is not liable for the property of the Customer and does not render services for its storage during the Event.<br />
    4.7. The damage caused by the guilty Party shall be reimbursed to the other Party in full.<br />
    4.8. The parties are exempt from liability under this Agreement in the event of force majeure.<br />
    4.9. The Contract comes into force from the moment it is signed by both Parties and is valid until the Parties fulfill their obligations in full.<br />
    4.10. In all other respects, what is not stipulated in this agreement, the parties are guided by the current legislation of the Russian Federation.
</div>

</br><div style="text-align: center;"><strong>5. Application for the celebration</strong></div><br />

<div style="text-align: center; padding-top: 5px;">
    <table border="1" bordercolor="#ABABAB" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
		<tr height="25px">
			<td>Date</td>
			<td>Time</td>
			<td>Place</td>
			<td>Children<br />quantity</td>
			<td>Age</td>
			<td>Animation program</td>
			<td>Customer&#39;s phone number</td>
		</tr>
		<tr height="25px">
            <td><?=date('d.m.Y', strtotime($this->row->date))?></td>
            <td><?=$this->row->foreign('timeId')->title?> - <?=date('H:i', strtotime($this->row->foreign('timeId')->title) + 60 * 60 * 2)?></td>
            <td><?=$this->row->foreign('placeId')->title?></td>
            <td><?=$this->row->childrenCount?></td>
            <td><?=$this->row->childrenAge?></td>
            <td><?=$this->row->foreign('programId')->title . ($this->row->foreign('subprogramId') ? ': ' . $this->row->foreign('subprogramId')->title : '')?>
                <br /><?=$this->row->foreign('timeId')->title?> - <?=date('H:i', strtotime($this->row->foreign('timeId')->title) + 60 * 60)?>
            </td>
            <td><?=$this->row->clientPhone?></td>
        </tr>
	</tbody>
    </table>
</div>
<br />
<strong>Other conditions:</strong> <?=$this->row->details?> <br />
<br />Total cost of services:<strong> &nbsp;<?=$this->row->modifiedPrice?$this->row->modifiedPrice:$this->row->price?> rubles</strong>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
An advance payment:<strong> <?=$this->row->managePrepay?> rubles</strong><br /><br />

<table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
	<tbody>
    <tr valign="top">
        <td width="50%">

			<div style="text-align: center;"><strong>Executor</strong></div>

			<strong>LLC &quot;Carousel-NN&quot;</strong><br />
			603104, N.Novgorod, Gagarin Ave., 35<br />
			INN 5261026806<br />
			R/s 40702810801010004480<br />
			OJSC &quot;NBD-Bank&quot;, Nizhny Novgorod<br />
			k/s 30101810400000000705&nbsp;&nbsp;
            BIC 042202705<br /><br />

			_________________ Mikhalko N.S.<br />
			M.P.
        </td>
        <td>
			<div style="text-align: center;"><strong>Customer</strong></div>
			<strong><?=$this->row->clientTitle?> </strong><br />
			Address: <?=str_pad($this->row->clientAddress, 180, ' ', STR_PAD_RIGHT)?><br />
			Passport: series&nbsp;<?=Indi::demo(false) ? $this->row->clientPassportNumber : substr($this->row->clientPassportNumber, 0, 4)?>
			number &nbsp;<?=Indi::demo(false) ? $this->row->clientPassportNumber : substr($this->row->clientPassportNumber, 4)?><br />
            issued &nbsp;<?=str_pad($this->row->clientPassportIssueInfo, 150, ' ', STR_PAD_RIGHT)?><br />
			phone: <?=$this->row->clientPhone?>
			<br /><br /><br />
			_________________<?=$this->row->clientTitle?>&nbsp;
        </td>
    </tr>
	</tbody>
</table>
