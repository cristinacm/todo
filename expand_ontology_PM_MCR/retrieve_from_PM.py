

# print(pred_matrix_split)

def create_out_dict(out, frame_col, LU_col, ILI_col):
	if frame_col not in out.keys():
		out[frame_col] = {}
	if LU_col not in out[frame_col].keys():
		out[frame_col][LU_col] = {"mcr":[], "LUs":""}
	if ILI_col not in out[frame_col][LU_col]["mcr"]:
		out[frame_col][LU_col]["mcr"].append(ILI_col)
	
	return out

def extract_ili_predmatrix_onlyoneframe(pred_matrix_split, frame, LUs, SUMOs):
	ilis_lu_PM = {}
	for pred in pred_matrix_split:
		# print(pred)
		frame_col = pred.split("\t")[12]
		# print(frame_col)
		LU_col = pred.split("\t")[13]
		SUMO_col = pred.split("\t")[19]
		ILI_col = pred.split("\t")[11]
		arg_col = pred.split("\t")[14]

		# print(LU_col)
		# print(SUMO_col)
		# print(frame_col)

		out = {}

		# for lu in LUs:
			# print(ILI_col)
		# if frame_col == frame:
			# print("ok")
		# if SUMO_col in SUMOs:
			# print("ok")
		# if LU_col in LUs:
			# print("ok")
		if frame_col == frame and SUMO_col in SUMOs and LU_col in LUs:
			
			# if frame_col not in out.keys():
				# out[frame] = {}
			# else:
				# continue
			# print("ok")
			if LU_col not in ilis_lu_PM.keys():
				# print("ok")
				ilis_lu_PM[LU_col] = [ILI_col]
				# print(ilis_lu_PM)
			elif ILI_col not in ilis_lu_PM[LU_col]:
				ilis_lu_PM[LU_col].append(ILI_col)
			
			# ilis_PM.append(ILI_col)
	# print(ilis_lu_PM)
		# if len(ilis_PM) < 1:
			# out[frame] = {}
				# out[frame][lu]
		out[frame] = ilis_lu_PM
		# print(out)
	return(out)


def extract_ili_predmatrix_morethanoneframe(pred_matrix_split, args):
	ilis_lu_PM = {}
	out = {}
	for pred in pred_matrix_split:
		# print(pred)
		frame_col = pred.split("\t")[12]
		# print(frame_col)
		LU_col = pred.split("\t")[13]
		SUMO_col = pred.split("\t")[19]
		ILI_col = pred.split("\t")[11]
		arg_col = pred.split("\t")[14]

		# print(LU_col)
		# print(SUMO_col)
		# print(frame_col)


		# for lu in LUs:
			# print(ILI_col)
		# if frame_col == frame:
			# print("ok")
		# if SUMO_col in SUMOs:
			# print("ok")
		# if LU_col in LUs:
			# print("ok")
		if frame_col in args.keys():
			if len(args[frame_col]["LUs"]) < 1 and len(args[frame_col]["SUMOs"]) < 1:
					out = create_out_dict(out, frame_col, LU_col, ILI_col[4:])
			if len(args[frame_col]["LUs"]) < 1:
				if SUMO_col in args[frame_col]["SUMOs"]:
					out = create_out_dict(out, frame_col, LU_col, ILI_col[4:])
			elif len(args[frame_col]["SUMOs"]) < 1:
				if LU_col in args[frame_col]["LUs"]:
					out = create_out_dict(out, frame_col, LU_col, ILI_col[4:])
			else:
				if SUMO_col in args[frame_col]["SUMOs"] and LU_col in args[frame_col]["LUs"]:
					out = create_out_dict(out, frame_col, LU_col, ILI_col[4:])
			
			
			
	# print(out)
			# else:
				# continue
			# print("ok")
			# if LU_col not in ilis_lu_PM.keys():
				# print("ok")
				# ilis_lu_PM[LU_col] = [ILI_col]
				# print(ilis_lu_PM)
			# elif ILI_col not in ilis_lu_PM[LU_col]:
				# ilis_lu_PM[LU_col].append(ILI_col)
			
			# ilis_PM.append(ILI_col)
	# print(ilis_lu_PM)
		# if len(ilis_PM) < 1:
			# out[frame] = {}
				# out[frame][lu]
			# out[frame_col] = ilis_lu_PM
			# print(out)
	# print(out)
	return(out)







# frame = "fn:Motion"
# LUs = ["fn:go.v", "fn:move.v"] #if len > 1 -> all
# SUMOs = ["mcr:Motion", "mcr:DirectionChange", "mcr:BodyMotion"] #if len > 1 -> all
# print(extract_ili_predmatrix_onlyoneframe(pred_matrix_split, frame, LUs, SUMOs))

# args = {"fn:Motion": {"LUs": ["fn:go.v", "fn:move.v"], "SUMOs": ["mcr:Motion", "mcr:DirectionChange", "mcr:BodyMotion"]}, "fn:Taking": {"LUs": ["fn:take.v", "NULL"], "SUMOs": ["mcr:Guiding", "mcr:Transfer"]}}